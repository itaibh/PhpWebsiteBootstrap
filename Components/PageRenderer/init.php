<?php
    define('APACHE_MIME_TYPES_URL','http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types');

    class PageRenderer extends ControllerComponentBase implements IPageRenderer
    {
        public function GetRouteName() { return null; }

        private $template_file;
        private $theme;
        private $theme_path;

        public function Init($init_data)
        {
            self::getLogger()->log_info("initializing page renderer");
            $this->template_file = $init_data['default_template'];
            $this->theme = $init_data['theme'];
            $this->theme_path = ROOTPATH . '/Themes/' . $this->theme . '/';

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

        private $current_path;
        private $current_query;

        public function HandleRequest($path, $query)
        {
            if ($path[1] == 'CurrentTheme') {
                $filename = implode('/', array_slice($path,2));
                $mimetype = $this->GetMimeFromExtension(pathinfo($filename, PATHINFO_EXTENSION));
                header('Content-type: '.$mimetype);
                include $this->theme_path . $filename;
            } else {
                $filepath = $this->theme_path . 'page-' . $path[1] . '.php';
                if (file_exists($filepath)) {
                    include $filepath;
                } else {
                    $this->current_path = $path;
                    $this->current_query = $query;

                    $page = $path[1];
                    $filepath = $this->theme_path . $page . '.php';
                    if (file_exists($filepath)) {
                        include $filepath;
                    }

                    include $this->theme_path . $this->template_file;
                }
            }
        }

        private $section_names = array();
        private $section_data = array();

        private function StartSection($name)
        {
            $this->section_names[] = $name;
            ob_start(function ($buffer){
                $name = array_pop($this->section_names);
                $this->section_data[$name] = $buffer;
            });
        }

        private function EndSection(){
            ob_end_flush();
        }

        private function RenderSection($section)
        {
            if (isset($this->section_data[$section])){
                echo $this->section_data[$section];
            }
        }

        private function RenderPart($part)
        {
            include(ROOTPATH."/Views/Parts/{$part}.php");
            /*if (isset($this->part_data[$part])){
                echo $this->part_data[$part];
            }*/
        }

        private $required_scripts = array();

        private function RequireScript($scriptname){
            $this->required_scripts[$scriptname] = null;
        }

        private function RenderRequiredScripts(){
            foreach ($this->required_scripts as $key => $value) {
                echo "<script src=\"$key\"></script>";
            }
        }

        private $required_styles = array();

        private function RequireStyle($styletname){
            $this->required_styles[$styletname] = null;
        }

        private function RenderRequiredStyles(){
            foreach ($this->required_styles as $key => $value) {
                echo "<link rel=\"stylesheet\" href=\"$key\" />";
            }
        }

    }
?>
