<?php
    define('APACHE_MIME_TYPES_URL','http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types');

    class PageRenderer extends ControllerComponentBase implements IPageRenderer
    {
        public function GetRouteName() { return null; }

        private $template_file;
        private $theme;

        public function Init($init_data)
        {
            self::getLogger()->log_info("initializing page renderer");
            $this->template_file = $init_data['default_template'];
            $this->theme = $init_data['theme'];

            $this->generateUpToDateMimeArray(APACHE_MIME_TYPES_URL);
        }

        //http://webcache.googleusercontent.com/search?q=cache:5ClzClyT0mMJ:php.net/manual/en/function.mime-content-type.php+&cd=2&hl=en&ct=clnk&gl=il&client=ubuntu
        private function generateUpToDateMimeArray($url){
            foreach(explode("\n",file_get_contents($url))as $x){
                if(isset($x[0])&&$x[0]!=='#'&&preg_match_all('#([^\s]+)#',$x,$out)&&isset($out[1])&&($c=count($out[1]))>1){
                    for($i=1;$i<$c;$i++){
                        $this->mimetypes[$out[1][$i]] = $out[1][0];
                    }
                }
            }
        }

        private $mimetypes;
        public function GetMimeFromExtension($extension) {
            if (isset($this->mimetypes[$extension]))
                return $this->mimetypes[$extension];

            return "application/octet-stream";
        }

        public function HandleRequest($path, $query)
        {
            $themePath = ROOTPATH . '/Themes/' . $this->theme . '/';
            if ($path[1] == 'CurrentTheme') {
                $filename = implode('/', array_slice($path,2));
                $mimetype = $this->GetMimeFromExtension(pathinfo($filename, PATHINFO_EXTENSION));
                header('Content-type: '.$mimetype);
                include $themePath . $filename;
            } else {
                $filepath = $themePath . 'page-' . end($path) . '.php';
                if (file_exists($filepath)) {
                    include $filepath;
                } else {
                    include $themePath . $this->template_file;
                }
            }
        }

    }
?>
