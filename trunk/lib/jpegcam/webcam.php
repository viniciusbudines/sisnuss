<?php

/* JPEGCam Test Script */
/* Receives JPEG webcam submission and saves to local file. */
/* Make sure your directory has permission to write files as your web server user! */
$filedata = file_get_contents('php://input');
$filename = '../../fotos/temp.jpg';
$result = file_put_contents( $filename, $filedata );
if (!$result) {
    print "ERRO: Falha ao gravar dados em $filename, checar\n";
    exit();
}
echo "sucesso";
exit();

?>
