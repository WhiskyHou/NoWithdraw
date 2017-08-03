<!DOCTYPE html>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>微信防撤回</title>
        <link href="http://xxhouyi.cn/xxhouyi_files/logo_640.jpg" rel="shortcut icon">
        <style> 
            body{ text-align:center}
        </style> 
    </head>
    <body>
        <?php
            //exec('rm -f QR.png');
            //pclose(popen('./go.sh','r'));
            //$count = 0;
            // shell_exec('python3 1.py');
            //sleep(5);
            while(1) {
                if (file_exists("QR.png")){
                    break;
                }
            }
            while(1) {
                if (file_exists("QR.png")){
                    echo "<br/><br/><br/><br/><br/><div class=\"div\">请尽快扫描，如果登录没反应，请点击二维码返回重试</div><br/><br/>";
                    echo "<a id=\"go-back-home\" href=\"http://xxhouyi.cn\"><img src=\"./QR.png\" alt=\"QR\" width=\"260\" height=\"260\"></a>";
                    break;
                }
            }
        ?>
    </body>
</html>