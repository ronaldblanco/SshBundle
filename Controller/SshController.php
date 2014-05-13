<?php

namespace Acme\Bundle\SshBundle\Controller;

use Acme\Bundle\LoginBundle\Controller\DefaultMainController;

use Acme\Bundle\SshBundle\Lib\ssh_con_exe;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class SshController extends DefaultMainController
{
    
    public function indexAction($aarray = null)
    {
    	$title= 'Ssh';
    	$data = array('title'=> $title, 'miarray'=> $aarray);
    	//if ($this->admacceso()) {
			return $this->render('AcmeSshBundle:Ssh:bash_view-twig.html.twig', $data);
	//	} else return $this->render('AcmeLoginBundle:Autentication:error.html.twig', $data);
    }
	
	public function bashcmdAction()
	{
		$title= 'Ssh';
    	$data = array('title'=> $title);
		//if ($this->admacceso()) {
			//session 
			$session = $this->getRequest()->getSession();
			//Tomando datos del formulario
			$request = Request::createFromGlobals();
			$server=$request->request->get('server', 'localhost');
			$savehost=$request->request->get('savehost', NULL);
			$pass=$request->request->get('password', NULL);
			$savepass=$request->request->get('savepass', NULL);
			$cmd=$request->request->get('cmd', 'date');
			$time=$request->request->get('time', 5);
			$noout =$request->request->get('noout', NULL);
		
			if ($savehost) $session->set('savehost', $server);
			if ($savepass) $session->set('savepass', $this->miencrypt($this->miencrypt($pass)));
		
			if ($session->get('savehost')) $server= $session->get('savehost');
			if ($session->get('savepass')) $pass = $this->midecrypt($this->midecrypt($session->get('savepass')));
		
			if ($server && $pass && $cmd && $time) {
				$ssh = new ssh_con_exe($server,22);
				$ssh->ssh_bind('root', $pass);
				if (!$noout) $data['miarray'] = explode("$", $ssh->ssh_exe($cmd,$time));
				if ($noout) $data['miarray'] = explode("$", $ssh->ssh_exe_noout($cmd,$time));
				if (is_array($data['miarray']) && count($data['miarray']) > 1) $this->get('session')->setFlash('salida', 'Comando ejecutado correctamente!');
				if (!$ssh->get_error() && $data['miarray'][0] == '' && count($data['miarray']) == 1) $this->get('session')->setFlash('error', 'El comando especificado no produjo salida alguna, rectifiquelo!');
				if ($ssh->get_error()) $this->get('session')->setFlash('error', $ssh->get_error());
				$ssh->close();
			} else {
				$this->get('session')->setFlash('error', 'Se encontró errores en los datos, no fué posible continuar!');
			}
			
			$data['cmd'] = $cmd;
			return $this->render('AcmeSshBundle:Ssh:bash_view-twig-OK.html.twig', $data);
			//return $this->redirect($this->generateUrl('ssh',$myarrout));
		//} else return $this->render('AcmeLoginBundle:Autentication:error.html.twig', $data);
	}

	public function ficheroAction()
	{
		$title= 'Trabajo con Ficheros';
    	$data = array('title'=> $title);
		//if ($this->admacceso()) {
			return $this->render('AcmeSshBundle:Ssh:file_view.html.twig', $data);
		//} else return $this->render('AcmeLoginBundle:Autentication:error.html.twig', $data);
	}
	
	public function ficheroreadAction()
	{
		$title= 'Trabajo con Ficheros';
    	$data = array('title'=> $title);
		//if ($this->admacceso()) {
			//session 
			$session = $this->getRequest()->getSession();
			//Tomando datos del formulario
			$request = Request::createFromGlobals();
			$server=$request->request->get('server', 'localhost');
			$savehost=$request->request->get('savehost', NULL);
			$pass=$request->request->get('password', NULL);
			$savepass=$request->request->get('savepass', NULL);
			$local=$request->request->get('local', NULL);
			
			if ($local) $session->set('remotefile', $local);
						
			if ($savehost) $session->set('savehost', $server);
			if ($savepass) $session->set('savepass', $this->miencrypt($this->miencrypt($pass)));
		
			if ($session->get('savehost')) $server= $session->get('savehost');
			if ($session->get('savepass')) $pass = $this->midecrypt($this->midecrypt($session->get('savepass')));
			
			if ($server && $pass && $local) {
				$ssh = new ssh_con_exe($server,22);
				$ssh->ssh_bind('root', $pass);
				//$temparr=explode("$",$ssh->ssh_exe('cat -E '.$local,10));
				$result = $ssh->ssh_exe('cat -E '.$local,10);
				//var_dump($result);
				$data['miarray']=explode("$",$result);
				$ssh->close();
				
				if (!$ssh->get_error() && $result != NULL) {
					$this->get('session')->setFlash('salida', 'Se lleyó el fichero correctamente!');
					return $this->render('AcmeSshBundle:Ssh:file_view_read.html.twig', $data);
				}
				else $this->get('session')->setFlash('error', $ssh->get_error()." No se obtuvo lectura del fichero, revise si este existe!");
				
				
			}else $this->get('session')->setFlash('error', 'Se encontró errores en los datos, no fué posible continuar!');
			
			return $this->render('AcmeSshBundle:Ssh:file_view.html.twig', $data);
			
		//} else return $this->render('AcmeLoginBundle:Autentication:error.html.twig', $data);
	}

	public function ficherowriteAction()
	{
		$title= 'Trabajo con Ficheros';
    	$data = array('title'=> $title);
		//if ($this->admacceso()) {
			//session 
			$session = $this->getRequest()->getSession();
			//Tomando datos del formulario
			$request = Request::createFromGlobals();
			$local=$request->request->get('mitexto', NULL);
			$file =$session->get('remotefile');
			
			$arrtext = explode("$", $local);		
			//var_dump($arrtext);			
			if ($session->get('savehost')) $server= $session->get('savehost');
			if ($session->get('savepass')) $pass = $this->midecrypt($this->midecrypt($session->get('savepass')));
			
			if ($server && $pass && $local && $file) {
				$ssh = new ssh_con_exe($server,22);
				$ssh->ssh_bind('root', $pass);
				
				$ssh->ssh_exe_noout("echo '#' > ".$file.".save");
				foreach ($arrtext as $line) {
					$ssh->ssh_exe_noout("echo '".trim($line)."' >> ".$file.".save");
				}
				$ssh->ssh_exe("mv ".$file." ".$file.".save-old");
				$ssh->ssh_exe("mv ".$file.".save ".$file);	
				
				$ssh->close();
				
				if (!$ssh->get_error())$this->get('session')->setFlash('salida', 'Se modificó el fichero correctamente!');
				else $this->get('session')->setFlash('error', 'Error al modificar el fichero!');
			}
			
			return $this->render('AcmeSshBundle:Ssh:file_view.html.twig', $data);
			
		//} else return $this->render('AcmeLoginBundle:Autentication:error.html.twig', $data);
	}

	public function cleansshcacheAction()
	{
		///session 
		$session = $this->getRequest()->getSession();
		
		$session->set('savehost', NULL);
		$session->set('savepass', NULL);
		
		return $this->redirect($this->generateUrl('ssh'));
	}
}
