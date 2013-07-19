<?php

/**
 * Editor plugin for Pico
 *
 * @author Gilbert Pellegrom
 * @link http://pico.dev7studios.com
 * @license http://opensource.org/licenses/MIT
 * @version 1.1
 */
class Pico_Editor {

	private $is_admin;
	private $is_logout;
	private $plugin_path;
	private $password;
	
	public function __construct()
	{
		$this->is_admin = false;
		$this->is_logout = false;
		$this->plugin_path = dirname(__FILE__);
		$this->password = '';
		session_start();
		
		if(file_exists($this->plugin_path .'/pico_editor_config.php')){
			global $pico_editor_password;
			include_once($this->plugin_path .'/pico_editor_config.php');
			$this->password = $pico_editor_password;
		}
	}
	
	public function request_url(&$url)
	{
		// Are we looking for /admin?
		if($url == 'admin') $this->is_admin = true;
		if($url == 'admin/new') $this->do_new();
		if($url == 'admin/open') $this->do_open();
		if($url == 'admin/save') $this->do_save();
		if($url == 'admin/delete') $this->do_delete();
		if($url == 'admin/logout') $this->is_logout = true;
	}
	
	public function before_render(&$twig_vars, &$twig)
	{
		if($this->is_logout){
			session_destroy();
			header('Location: '. $twig_vars['base_url'] .'/admin');
			exit;
		}
		
		if($this->is_admin){
			header($_SERVER['SERVER_PROTOCOL'].' 200 OK'); // Override 404 header
			$loader = new Twig_Loader_Filesystem($this->plugin_path);
			$twig_editor = new Twig_Environment($loader, $twig_vars);
			if(!$this->password){
				$twig_vars['login_error'] = 'No password set for the Pico Editor.';
				echo $twig_editor->render('login.html', $twig_vars); // Render login.html
				exit;
			}
				
			if(!isset($_SESSION['pico_logged_in']) || !$_SESSION['pico_logged_in']){
				if(isset($_POST['password'])){
					if(sha1($_POST['password']) == $this->password){
						$_SESSION['pico_logged_in'] = true;
					} else {
						$twig_vars['login_error'] = 'Invalid password.';
						echo $twig_editor->render('login.html', $twig_vars); // Render login.html
						exit;
					}
				} else {
					echo $twig_editor->render('login.html', $twig_vars); // Render login.html
					exit;
				}
			}
				
			echo $twig_editor->render('editor.html', $twig_vars); // Render editor.html
			exit; // Don't continue to render template
		}
	}
	
	private function do_new()
	{
		if(!isset($_SESSION['pico_logged_in']) || !$_SESSION['pico_logged_in']) die(json_encode(array('error' => 'Error: Unathorized')));
		$title = isset($_POST['title']) && $_POST['title'] ? strip_tags($_POST['title']) : '';
		$file = $this->slugify(basename($title));
		if(!$file) die(json_encode(array('error' => 'Error: Invalid file name')));
		
		$error = '';
		$file .= CONTENT_EXT;
		$content = '/*
Title: '. $title .'
Author: 
Date: '. date('Y/m/d') .'		
*/';
		if(file_exists(CONTENT_DIR . $file)){
			$error = 'Error: A post already exists with this title';
		} else {
			file_put_contents(CONTENT_DIR . $file, $content);
		}
		
		die(json_encode(array(
			'title' => $title,
			'content' => $content,
			'file' => basename(str_replace(CONTENT_EXT, '', $file)),
			'error' => $error
		)));
	}
	
	private function do_open()
	{
		if(!isset($_SESSION['pico_logged_in']) || !$_SESSION['pico_logged_in']) die(json_encode(array('error' => 'Error: Unathorized')));
		$file_url = isset($_POST['file']) && $_POST['file'] ? $_POST['file'] : '';
		
		$parse_file_url = parse_url($file_url);
		$file = $parse_file_url['path']; // Get path from $file_url
		if(!$file) die('Error: Invalid file');

		$file = CONTENT_DIR . $file; // Get file system path
		if(file_exists($file . CONTENT_EXT)) $file = $file . CONTENT_EXT; // Make sure samename/ doesn't override samename.md
		else if (is_dir($file) && file_exists($file . '/index' . CONTENT_EXT)) $file = $file . '/index' . CONTENT_EXT;
		else die('Error: Invalid file');
		
		die(file_get_contents($file));
	}
	
	private function do_save()
	{
		if(!isset($_SESSION['pico_logged_in']) || !$_SESSION['pico_logged_in']) die(json_encode(array('error' => 'Error: Unathorized')));
		$file_url = isset($_POST['file']) && $_POST['file'] ? $_POST['file'] : '';

		$parse_file_url = parse_url($file_url);
		$file = $parse_file_url['path']; // Get path from $file_url
		if(!$file) die('Error: Invalid file');
		
		$content = isset($_POST['content']) && $_POST['content'] ? $_POST['content'] : '';
		if(!$content) die('Error: Invalid content');
		
		$file = CONTENT_DIR . $file; // Get file system path
		if(file_exists($file . CONTENT_EXT)) $file = $file . CONTENT_EXT; // Make sure samename/ doesn't override samename.md
		else if (is_dir($file) && file_exists($file . '/index' . CONTENT_EXT)) $file = $file . '/index' . CONTENT_EXT;
		else die('Error: Invalid file');
		
		file_put_contents($file, $content);
		die($content);
	}
	
	private function do_delete()
	{
		if(!isset($_SESSION['pico_logged_in']) || !$_SESSION['pico_logged_in']) die(json_encode(array('error' => 'Error: Unathorized')));
		$file_url = isset($_POST['file']) && $_POST['file'] ? $_POST['file'] : '';

		$parse_file_url = parse_url($file_url);
		$file = $parse_file_url['path']; // Get path from $file_url
		if(!$file) die('Error: Invalid file');
		
		$file = CONTENT_DIR . $file; // Get file system path
		if(file_exists($file . CONTENT_EXT)) $file = $file . CONTENT_EXT; // Make sure samename/ doesn't override samename.md
		else if (is_dir($file) && file_exists($file . '/index' . CONTENT_EXT)) $file = $file . '/index' . CONTENT_EXT;
		else die('Error: Invalid file');
		
		die(unlink($file));
	}
	
	private function slugify($text)
	{ 
		// replace non letter or digits by -
		$text = preg_replace('~[^\\pL\d]+~u', '-', $text);
		
		// trim
		$text = trim($text, '-');
		
		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		
		// lowercase
		$text = strtolower($text);
		
		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);
		
		if (empty($text))
		{
		return 'n-a';
		}
		
		return $text;
	}
	
}

?>
