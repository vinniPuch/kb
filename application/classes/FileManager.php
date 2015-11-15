<?php

include 'simple_html_dom.php';

class FileManager {
    
    private $input_name;
    private $work_folder;
    private $file_data;
    private $dom;
    private $error;
        
    public function __construct($name, $folder = null){
        
        $this->input_name = $name;
        
        if( $folder === null ) {
            $this->work_folder = getcwd() . '/tmp/';
        }else{
            $this->work_folder = $folder;
        }
        
        $this->file_data = false;
    }
    
    public function Run() {
                
        session_start();
        
        if( isset( $_SESSION['file_manager_data']) ) {
            $this->file_data = unserialize($_SESSION['file_manager_data']);
        }
              
        if( isset($_GET['action']) ) {
            
            switch($_GET['action']) {
                case 'save':                $this->save();              die();
                case 'upload':              $this->upload();            break;
                case 'load_tpl':            $this->load_tpl();          break;
                case 'download':            $this->download();          die();
                case 'close':               $this->close();             die();
                case 'update_category':     $this->updateCategory();    die();
            }
            
        }
        
        $this->display();
      
        // in session save only information about file, not dom structure
        $_SESSION['file_manager_data'] = serialize($this->file_data);
        
    }
    
    public function upload() {
    
        if( isset($_FILES[$this->input_name]) ) {
            if($_FILES[$this->input_name]['error'] > 0) {
                $this->error = "Uploading error. Return code {$_FILES[$this->input_name]['error']}";
                
                return false;
                
            } else {
                $this->file_data = $_FILES[$this->input_name];
                $this->file_data["current_file_name"] = date('H:i:s d:m:Y') . '-' . $this->file_data['name'];
                
                if( !move_uploaded_file($this->file_data["tmp_name"], $this->work_folder . $this->file_data["current_file_name"] ) ){
                    
                    $this->error = "Moveing error";
                    return false;
                    
                } else {
                    
                    $this->file_data["uploaded"] = true;
                    $this->addCssToTmpFile();
                    
                    header("location:/");
                } 
            }

        }
        
        return true;
        
    }
    
    public function load_tpl(){
        
        $templates_names = array(
                                    1 => 'table-1column.html',
                                    2 => 'table-2column-2bold.html',
                                    3 => 'table-3column-2bold.html'
                                );
        
        $tpl_name = $templates_names[$_GET['id']];
        
        $this->file_data["current_file_name"] = date('H:i:s d:m:Y') . '-' . $tpl_name;

        if( copy( './html/templates/' . $tpl_name, $this->work_folder . $this->file_data["current_file_name"]) ){
            
            $this->file_data["uploaded"] = true;
            $this->addCssToTmpFile();
            
            header("location:/");
            
        } else {
            
            $this->error = "Copy error";
            return false;
            
        } 
        
    }
    
    public function addCssToTmpFile(){
        
        $file_path = $this->work_folder . $this->file_data["current_file_name"];
        
        $tmp_file = new simple_html_dom( $str = null, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = false);
        $tmp_file->load_file($file_path);
        
        $head = $tmp_file->find('head');
        $head[0]->innertext .= "<link rel='stylesheet' href='/css/style-frame.css'>";
        $head[0]->innertext .= "<link rel='stylesheet' href='/css/correction.css'>";
        
        file_put_contents($file_path, $tmp_file);
        
    }
    
    public function deleteCssFromTmpFile(){
                
        $file_path = $this->work_folder . $this->file_data["current_file_name"];
        
        $tmp_file = new simple_html_dom( $str = null, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = false);
        $tmp_file->load_file($file_path);
        
        $tmp_style = $tmp_file->find("[href=/css/style-frame.css]");
        $tmp_style[0]->outertext = '';

        $tmp_style = $tmp_file->find("[href=/css/correction.css]");
        $tmp_style[0]->outertext = '';
          
        file_put_contents($file_path, $tmp_file);
        
    }
    
    public function display(){
        
        $file_path = $this->work_folder . $this->file_data["current_file_name"];
        
        $this->dom = new simple_html_dom($str=null, $lowercase=true, $forceTagsClosed=true, $target_charset=DEFAULT_TARGET_CHARSET, $stripRN=false);
        $this->dom->load_file("./html/tpl.html");
        
        //attach html for menu
        $body = $this->dom->find('body');
                
        ob_start();
        
        extract(array('file_inited' => $this->file_data["uploaded"]));
        include './html/editor-menu.php';
            
        $menu = ob_get_clean();
                
        if ( $this->file_data["uploaded"] !== true ) {
            $body[0]->innertext = $menu . $body[0]->innertext;
        } else {
            
            $editable_iframe = "<iframe id='content-frame' src='/tmp/{$this->file_data["current_file_name"]}' ></iframe>";
            $body[0]->innertext = $menu . $editable_iframe;
        }
        
       echo $this->dom;
      
    }
    
    public function save() {
        
        $this->dom = new simple_html_dom($str = null, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = false);
        $this->dom->load($_POST["document"]);

        //delete controls add/remove row
        
        $editor_controls = $this->dom->find("[class=controls]");
        foreach($editor_controls as $control) {
            $control->outertext = '';
        }

        $file_path = $this->work_folder . $this->file_data["current_file_name"];

        if( file_put_contents($file_path, $this->dom) ) {
            
            echo 'true';
            die();
            
        }
        
        echo 'false';
        die();
        
    }
    
    public function getFileName(){

        $file_path = $this->work_folder . $this->file_data["current_file_name"];
        
        $tmp_file = new simple_html_dom( $str = null, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = false);
        $tmp_file->load_file($file_path);
        
        $sub_category = $tmp_file->find("[id=sub-category-sub-title]");
        
        return $this->convertToTranslit($sub_category[0]->innertext);

    }
    
    public function download() {
        
        $this->deleteCssFromTmpFile();
        
        $file_path = $this->work_folder . $this->file_data["current_file_name"];
        //escape ':' in file name
        $file_path = addcslashes($file_path ,": ");
        //formatting html in file
        shell_exec("tidy -m --indent auto --indent-spaces 3 -utf8 -wrap 0 --tidy-mark no $file_path");
                        
        
        $file = $this->work_folder . $this->file_data["current_file_name"];
        
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $this->getFileName() . ".html" );
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
        }
        
        $this->addCssToTmpFile();
        
    }
    
    public function close() {
       
       //unlink( $this->work_folder . $this->file_data["current_file_name"] );
       unset($_SESSION['file_manager_data']);
       session_destroy(); 
       session_unset($_SESSION['session_id']);
       
       header("location:/");
        
    }
    
    public function convertToTranslit( $string){

        static $convert = array(
            

		'й'=>'j',   'ц'=>'c',   'у'=>'u',   'к'=>'k',
                'е'=>'e',   'н'=>'n',   'г'=>'g',   'ш'=>'sh',
		'щ'=>'sh',  'з'=>'z',   'х'=>'h',   'ъ'=>'',
                'ф'=>'f',   'ы'=>'y',   'в'=>'v',   'а'=>'a',
		'п'=>'p',   'р'=>'r',   'о'=>'o',   'л'=>'l',
                'д'=>'d',   'ж'=>'zh',  'э'=>'e',   'я'=>'ja',
		'ч'=>'ch',  'с'=>'s',   'м'=>'m',   'и'=>'i',
                'т'=>'t',   'ь'=>'',    'б'=>'b',   'ю'=>'ju',
                'ё'=>'e',   'и'=>'i',

		'Й'=>'J',   'Ц'=>'C',   'У'=>'U',   'К'=>'K',
                'Е'=>'E',   'Н'=>'N',   'Г'=>'G',   'Ш'=>'SH',
		'Щ'=>'SH',  'З'=>'Z',   'Х'=>'H',   'Ъ'=>'',
                'Ф'=>'F',   'Ы'=>'Y',   'В'=>'V',   'А'=>'A',
		'П'=>'P',   'Р'=>'R',   'О'=>'O',   'Л'=>'L',
                'Д'=>'D',   'Ж'=>'ZH',  'Э'=>'E',   'Я'=>'JA',
		'Ч'=>'CH',  'С'=>'S',   'М'=>'M',   'И'=>'I',
                'Т'=>'T',   'Ь'=>'',    'Б'=>'B',   'Ю'=>'JU',  
                'Ё'=>'E',   'И'=>'I',
                
		'і'=>'i',   'ї' => 'i', 'b' => 'b', 'І' => 'i',
            

		' '=>'-',   '"'=>'',    '\t'=>'',   
                '«'=>'',    '»'=>'',    '?'=>'',    '!'=>'', 
                "."=>".",   "/"=>"_",   "\\"=>"_",  "*"=>"_",
                ":"=>":",   "\""=>"_",  "<"=>"_",   ">"=>"_",
                "|"=>"_"
            
	);
        
	return  mb_strtolower( strtr( $string, $convert ) );
        
    }
    
    public function updateCategory(){
        
        $category = null;
        preg_match_all('/^.*\/(.*)\..*$/', $_SERVER ["HTTP_REFERER"], $category);
        shell_exec("cd ../base/category/; ./make-category.bash -cat-up {$category[1][0]};");
        header("Location: {$_SERVER['HTTP_REFERER']} ");
        
    }
   
}
