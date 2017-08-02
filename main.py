# coding=utf-8
import os
import re
import sys
import shutil
import time
import itchat
from itchat.content import *


msg_dict = {}


def ClearTimeOutMsg():
    if msg_dict.__len__() > 0:
        for msgid in list(msg_dict):
            if time.time() - msg_dict.get(msgid, None)["msg_time"] > 130.0:
                item = msg_dict.pop(msgid)
                if item['msg_type'] == "Attachment" or item['msg_type'] == "Recording" or item['msg_type'] == "Picture" or item['msg_type'] == "Video":
                    os.remove(item['msg_content'])


@itchat.msg_register([TEXT, PICTURE, CARD, SHARING, RECORDING, ATTACHMENT, VIDEO, FRIENDS])
def Revocation(msg):
    # userInfo = itchat.web_init()
    # myname = userInfo['NickName']


    mytime = time.localtime()
    msg_time_touser = mytime.tm_year.__str__() + "/" + mytime.tm_mon.__str__() + "/" \
                      + mytime.tm_mday.__str__() + " " + mytime.tm_hour.__str__() + ":" \
                      + mytime.tm_min.__str__() + ":" + mytime.tm_sec.__str__()

    msg_id = msg['MsgId']
    msg_time = msg['CreateTime']
    msg_from = itchat.search_friends(userName=msg['FromUserName'])['NickName']
    msg_type = msg['Type']
    msg_content = None
    msg_url = None
    # Save Files
    if msg['Type'] == 'Text':
        msg_content = msg['Text']
    elif msg['Type'] == 'Picture':
        msg_content = msg['ToUserName']+msg['FileName']
        msg['Text'](msg['ToUserName']+msg['FileName'])
        # msg_content = myname + msg['FileName']
        # msg['Text'](myname + msg['FileName'])
    elif msg['Type'] == 'Card':
        msg_content = msg['RecommendInfo']['NickName'] + r" 的名片"
    elif msg['Type'] == 'Sharing':
        msg_content = msg['Text']
        msg_url = msg['Url']
    elif msg['Type'] == 'Recording':
        msg_content = msg['FileName']
        msg['Text'](msg['FileName'])
    elif msg['Type'] == 'Attachment':
        msg_content = r"" + msg['FileName']
        msg['Text'](msg['FileName'])
    elif msg['Type'] == 'Video':
        msg_content = msg['FileName']
        msg['Text'](msg['FileName'])
    elif msg['Type'] == 'Friends':
        msg_content = msg['Text']

    # Update
    msg_dict.update(
        {msg_id: {"msg_from": msg_from, "msg_time": msg_time, "msg_time_touser": msg_time_touser, "msg_type": msg_type,
                  "msg_content": msg_content, "msg_url": msg_url}})
    ClearTimeOutMsg()

# CheckNOTE


@itchat.msg_register([NOTE])
def SaveMsg(msg):
    if not os.path.exists("./Revocation/"):
        os.mkdir("./Revocation/")

    if re.search(r"\<replacemsg\>\<\!\[CDATA\[.*撤回了一条消息\]\]\>\<\/replacemsg\>", msg['Content']) != None:
        old_msg_id = re.search("\<msgid\>(.*?)\<\/msgid\>", msg['Content']).group(1)
        old_msg = msg_dict.get(old_msg_id, {})
        msg_send = r"这个人：" + old_msg.get('msg_from') \
                   + r"  在 [" + old_msg.get('msg_time_touser') \
                   + r"], 撤回了一条消息, 以下:" \
                   + old_msg.get('msg_content')
        if old_msg['msg_type'] == "Sharing":
            msg_send += r", 链接: " + old_msg.get('msg_url')
        elif old_msg['msg_type'] == 'Picture' \
                or old_msg['msg_type'] == 'Recording' \
                or old_msg['msg_type'] == 'Video' \
                or old_msg['msg_type'] == 'Attachment':
            if old_msg['msg_type'] == 'Picture':
                itchat.send('@img@%s' % old_msg['msg_content'] , toUserName='filehelper')
            if old_msg['msg_type'] == 'Video':
                itchat.send('@vid@%s' % old_msg['msg_content'] , toUserName='filehelper')
            if old_msg['msg_type'] == 'Recording' or old_msg['msg_type'] == 'Attachment':
                itchat.send('@fil@%s' % old_msg['msg_content'] , toUserName='filehelper')
            shutil.move(old_msg['msg_content'], r".\\Revocation\\")
        itchat.send(msg_send, toUserName='filehelper')

        msg_dict.pop(old_msg_id)
        ClearTimeOutMsg()


def output_info(msg):
    print('[INFO] %s' % msg)


def open_QR():
    for get_count in range(10):
        output_info('Getting uuid')
        uuid = itchat.get_QRuuid()
        while uuid is None: uuid = itchat.get_QRuuid();time.sleep(1)
        output_info('Getting QR Code')
        if itchat.get_QR(uuid): break
        elif get_count >= 9:
            output_info('Failed to get QR Code, please restart the program')
            sys.exit()
    output_info('Please scan the QR Code')
    return uuid

uuid = open_QR()
waitForConfirm = False
while 1:
    status = itchat.check_login(uuid)
    if status == '200':
        break
    elif status == '201':
        if waitForConfirm:
            output_info('Please press confirm')
            waitForConfirm = True
    elif status == '408':
        output_info('Reloading QR Code')
        uuid = open_QR()
        waitForConfirm = False
userInfo = itchat.web_init()
itchat.show_mobile_login()
itchat.get_friends(True)
output_info('Login successfully as')
itchat.start_receiving()

@itchat.msg_register
def simple_reply(msg):
    if msg['Type'] == 'Text':
        return 'I received: %s' % msg['Content']
itchat.run()
