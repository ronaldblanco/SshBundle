<?php

	/**
	 * Clase para conectar ssh y ejecutar comandos remotos
	 * autor: Ronald Blanco Carrazana
	 */
	 
namespace Acme\Bundle\SshBundle\Lib;


	class ssh_con_exe{
		
		var $methods = array(
	  		'kex' => 'diffie-hellman-group1-sha1',
  			'client_to_server' => array(
  			'crypt' => '3des-cbc',
  			'comp' => 'none'),
  			'server_to_client' => array(
  			'crypt' => 'aes256-cbc,aes192-cbc,aes128-cbc',
  			'comp' => 'none'));
		var $rtmp;
		var $tmp;
		
		var $server;
		var $port;
		
		var $conn = NULL;
		var $bind = NULL;
		var $exe = NULL;
		var $scp = NULL;
		var $error = NULL;
		
		function __construct($server = 'localhost', $port = 22) {
			
			$this->rtmp='/tmp/resulthgf-.txt';
	 		$this->tmp='/usr/tmp/resulthgf-.txt';
			
			$this->server=$server;
			$this->port=$port;
			
			//Conectar
			$this->conn = ssh2_connect($this->server, $this->port, $this->methods);
			if (!$this->conn) $this->error = $this->error.' Connection failed$';
			
		}
		
		public function ssh_bind($user = 'root',$pass = '')
		{
			if ($this->conn) {
				$this->bind = ssh2_auth_password($this->conn, $user, $pass);
				if (!$this->bind) $this->error = $this->error.' Autentication failed$';
			}
			
		}
		
		public function ssh_exe($cmd = "echo 'Ningun comando fue especificado!'", $time = 5)
		{
			$out=NULL;
		if ($this->conn && $this->bind) {//echo "Conexion correcta!";
			
			$this->exe = ssh2_exec($this->conn, $cmd.' > '.$this->rtmp);
			if (!$this->exe) $this->error = $this->error.' ssh execution failed$';
			sleep($time);
			$this->scp=ssh2_scp_recv($this->conn, $this->rtmp, $this->tmp);
			if (!$this->scp) $this->error = $this->error.' File transfer failed$';
			else {
				$out = shell_exec('cat -E '.$this->tmp);
			}
		}
		return $out;
		}
		
		public function ssh_exe_noout($cmd = "echo 'Ningun comando fue especificado!'", $time = 5)
		{
			$out="El comando no generó salida!$";
		if ($this->conn && $this->bind) {//echo "Conexion correcta!";
			
			$this->exe = ssh2_exec($this->conn, $cmd);
			if (!$this->exe) $this->error = $this->error.' ssh execution failed$';
			//sleep($time);
			//$this->scp=ssh2_scp_recv($this->conn, $this->rtmp, $this->tmp);
			/*if (!$this->scp) $this->error = $this->error.' File transfer failed$';
			else {
				$out = shell_exec('cat -E '.$this->tmp);
			}*/
		}
		return $out;
		}
		
		/*public function ssh_readfile($file = "", $time = 5)
		{
			$out=NULL;
		if ($this->conn && $this->bind) {//echo "Conexion correcta!";
			
			$this->exe = ssh2_exec($this->conn, 'cat '.$file.' > '.$this->rtmp);
			if (!$this->exe) $this->error = $this->error.' ssh read file failed$';
			sleep($time);
			$this->scp=ssh2_scp_recv($this->conn, $this->rtmp, $this->tmp);
			if (!$this->scp) $this->error = $this->error.' temp File transfer failed$';
			else {
				$out = shell_exec('cat -E '.$this->tmp);
			}
		}
		return $out;
		}*/
		
		public function ssh_writefile($lines = array(), $file)
		{
			$out=NULL;
			
			$my_file = fopen ($this->tmp, w);
			foreach ($lines as $line) {
				fwrite ($my_file, $line);
			}
			fclose($my_file);
			
			if ($this->conn && $this->bind) {//echo "Conexion correcta!";
				$out = $this->ssh_send($this->tmp,$file);
			}
			if ($out) return $out;
			else return $this->get_error();
		}
		
		public function ssh_send($local='',$remote='')
		{
			$out=NULL;
		if ($this->conn && $this->bind) {//echo "Conexion correcta!";
			
			$this->scp=ssh2_scp_send($this->conn, $local, $remote, 0644);
			if (!$this->scp) $this->error = $this->error.' File transfer failed for remote server$';
			else {$out = 'Fichero enviado con éxito!';}
			}
		return $out;
		}
		
		public function ssh_recv($remote='', $local='')
		{
			//var_dump($remote);
			//var_dump($local);
			$out='';
		if ($this->conn && $this->bind) {//echo "Conexion correcta!";
			
			$this->scp=ssh2_scp_recv($this->conn, $remote, $local);
			if (!$this->scp) $this->error = $this->error.' File transfer failed for local server$';
			else {$out = 'Fichero recibido localmente con éxito!';}
			}
		return $out;
		}
		
		public function close()
		{
			// Add this to flush buffers/close session
  			ssh2_exec($this->conn, 'exit'); 
		}

		public function get_error()
		{
			return $this->error;
		}
		
		public function get_last_cmd()
		{
			return shell_exec('cat -E '.$this->tmp);
		}

	}
	