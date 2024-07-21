<?php

/**********************************************************

MIT License
Copyright (c) 2024 Keden92
https://github.com/Keden92/php-asi/
Release: V5.1 (BETA) 13.07.2024

**********************************************************/

namespace asi
{
	// Use async-class ONLY to interact with child instances or to manage child threads!
	// All methods are static.
	// This class is only a wrapper to relieve the developer from managing object references.
	// Any of these function calls can throw exceptions!
	
	class async 
	{
		// Wait for a variable managed by a child thread to become usable
		public static function wait(&$obj)
		{
			if(core::is_asi($obj)) $obj->__wait($obj);
			return $obj;
		}
		
		// Check if a variable managed by a child thread is usable
		public static function ready(&$obj)
		{
			if(core::is_asi($obj)) return $obj->__ready($obj);
			return true;
		}
		
		// Enable or disable the autowait function when interacting with the child thread
		public static function autowait(&$obj, $state)
		{
			if(core::is_asi($obj)) $obj->__autowait($state);
		}
		
		// Check if the child thread is doing some work or "sleeping" and waiting to get some workload.
		public static function inuse(&$obj)
		{
			if(core::is_asi($obj)) return $obj->__inuse();
			return false;
		}
		
		// Shut down the child thread and clean up
		public static function unset(&$obj)
		{
			if(core::is_asi($obj)) $obj->__close();
		}
		
		// Create a new child-thread instance 
		// Pass a name (string) by which it can be identified in the process list ($instance_name)
		// Pass an array of strings with files that should be included in the child thread
		public static function new($instance_name, $additional_includes = null)
		{
			$x = new async_object($obj, $instance_name, new \stdClass, $additional_includes);
			return $obj;
		}
		
		//Debug Only -> Date Time Stamp
		public static function dts()
		{
			$now = \DateTime::createFromFormat('U.u', \microtime(true));
			echo $now->format("d-m-Y H:i:s.u")."<br/>";
		}
	}
	
	class core // async class interface basement
	{
		public static function otp($data)
		{
			return __NAMESPACE__.'\core::usd("'.core::sdo($data).'")';
		}
			
		public static function usd($data)
		{
			return \unserialize(\base64_decode($data));
		}
		
		public static function sdo($data)
		{
			return \base64_encode(\serialize($data));
		}
		
		public static function nvn()
		{
			return 'd'.\bin2hex(\random_bytes(5));
		}
		
		public static function is_asi($obj)
		{
			if(\gettype($obj) == 'object')
			{
				if(\preg_match('/('.__NAMESPACE__.'\\\\dyn_\w+_ghost)|('.__NAMESPACE__.'\\\\ghost)/', \get_class($obj)))
				{
					return true;
				}
			}
			return false;
		}
		
		public static function useable_var($var)
		{
			if(\gettype($var) != 'object') return true;
			$ob = new refl_core($var);
			if(($ob->hasMethod('__serialize') && $ob->hasMethod('__unserialize')) ||
				($ob->hasMethod('__sleep') && $ob->hasMethod('__wakeup')) ||
				($ob->hasMethod('serialize') && $ob->hasMethod('unserialize'))) return true;
			/*try {
		        if($var == \unserialize(\serialize($var))) return true;
		        return false;
		    } catch( Exception $e ) {
		        return false;
		    }*/
		    return false;
		}
	}

	class byref
	{
		public $value;
		public $read;
		
		function __construct(&$var)
		{
			$this->value = &$var;
			$this->read = $var;
		}
	}
	
	class method
	{
		public $method;
		public $vars;
		
		function __construct($m, &$d)
		{
			$this->method = $m;
			$this->vars = &$d;
		}
	}
	
	class var_core
	{
		private $var;
		private $name;
		
		public function __construct(&$var, $varname)
		{
			$this->var = &$var;
			$this->name = $varname;
		}
		
		public function is_me(&$var)
		{
			if($var !== $this->var) return false;
			$st = $var;
			$var = $this->var === true ? false : true;
			$same = $var === $this->var ? 1 : 0;
			$var = $st;
			return $same;
		}
		
		public function __get($name)
		{
			return $this->$name;
		}
	}

	class refl_core extends \ReflectionClass
	{
		private $asi_method;
		
		public function MethodExist($method)
		{    
			$methoden = $this->getMethods();
			foreach($methoden as $methode)
			{
				if($methode->name == $method)
				{
					$this->asi_method = $methode->name;
					return true;	
				}
			}
			foreach($methoden as $methode)
			{
				if($methode->name == "__call")
				{
					$this->asi_method = $methode->name;
					return true;	
				}
			}
			return false;
		}
		
		public function MethodParams()
		{
			return $this->getMethod($this->asi_method)->getParameters();
		}
		
		public function isExtendable()
		{
			if($this->hasMethod("__construct"))
			{
				if($this->isFinal() === false &&
				   $this->isAbstract() === false &&
				   $this->inNamespace() === false &&
				   $this->getMethod("__construct")->isFinal() === false) 
				   return true;
			}
			else
			{
				if($this->isFinal() === false &&
				   $this->isAbstract() === false &&
				   $this->inNamespace() === false) 
				   return true;
			}
			return false;
		}
	}

	class byref_core
	{
		private $value;
		private $lval;
		private $trans_id;
		
		public function __construct(&$ref_var, $trans_id)
		{
			$this->trans_id = $trans_id;
			$this->value = &$ref_var;
			$this->lval = $this->value;
			echo "#".$this->trans_id."#:".core::sdo($this->value).PHP_EOL; //initial broadcast
		}
		
		public function ck()
		{
			if($this->lval === $this->value) return;
			echo "#".$this->trans_id."#:".core::sdo($this->value).PHP_EOL; //broadcast on change
			$this->lval = $this->value;
		}
	}
	
	class ghost_core extends core
	{
		public function wrap_cls($cl_name, $ob_t)
		{
			$cl_name_wrap = __NAMESPACE__.'\dyn_'.$cl_name.'_ghost';
			if(!\class_exists($cl_name_wrap))
			{
				$met_arr = array();
				foreach ($ob_t->getMethods() as $methode)
				{
					$par_def = array();
					$has_byref = false;
					foreach ($ob_t->getMethod($methode->name)->getParameters() as $parm)
					{
						if(!$has_byref) $has_byref = $parm->isPassedByReference();
						$par_def[$parm->name] = array("variadic" => $parm->isVariadic(), "byref" => $has_byref);
					}
					if(\count($par_def) > 0 && $has_byref) $met_arr[$methode->name] = $par_def;
				}
				
				$cl_core = 'namespace '.__NAMESPACE__.'{';
				$cl_core .= 'class '.\str_replace(__NAMESPACE__."\\","",$cl_name_wrap).' extends ghost {';
				
				if(\count($met_arr)>0)
				{
					foreach($met_arr as $methode => $parms)
					{
						$cl_core .= 'public function '.$methode.'(';
						$in_var_arr = array();
						$out_var_arr = array();
						$exc_base = "";
						foreach($parms as $name => $vars)
						{
							$def = "";
							if($vars["byref"]) $def .= "&";
							if($vars["variadic"]) $def .= "...";
							$def .= "\$".$name;
							$in_var_arr[] = $def;
							if($vars["variadic"]) $out_var_arr[] = "...$".$name;
							else $out_var_arr[] = "$".$name;
							if($vars["byref"])
							{
								if($vars["variadic"])
									$exc_base .= 'foreach($'.$name.' as $id => &$var){$var = new byref($var);}';
								else
									$exc_base .= '$'.$name.' = new byref($'.$name.');';
							}
						}
						$cl_core .= \implode(",",$in_var_arr);
						$cl_core .= '){'.$exc_base;
						$cl_core .= 'return parent::'.$methode.'('.\implode(",",$out_var_arr).');}';
					}		
				}
				$cl_core .= '}}';
				eval($cl_core);
			}
			return $cl_name_wrap;
		}
	}

	class ghost extends ghost_core // async class interface ghost_class 
	{
		private $asc;
		private $vari;
		private $ob_t;
		private $me;
		private $myvars;
		
		function __construct(&$asc, &$vari, &$me, &$ob_t = null)
		{
			$this->asc = &$asc;
			$this->vari = &$vari;
			$this->me = &$me;
			$this->ob_t = $ob_t;
			$this->myvars = array();
		}
		
		function &__invoke(...$args)
		{
			return $this->__call("__invoke", $args);
		}
		
		function &__call($method, $args)
		{
			$this->chk();
			
			if($this->ob_t->MethodExist($method))
			{
				$byref = array();
				$parms = $this->ob_t->MethodParams();
				foreach ($parms as $parm)
				{
					$arg_pos = $parm->getPosition();
					if($parm->isVariadic() && $parm->isPassedByReference())
					{	
						foreach($args as $id => &$arg)
						{
							if($id >= $arg_pos)
							{
								if(\gettype($arg) != 'object') throw new \Exception('Error: byref value = false type.');
								if(\get_class($arg) != __NAMESPACE__.'\byref') throw new \Exception('Error: byref value = false type.');
								$byref[$id] = $this->byref_var($arg->value);
							}
						}
					}
					else
					{
						if($parm->isPassedByReference())
						{	
							if(\gettype($args[$arg_pos]) != 'object') throw new \Exception('Error: byref value = false type.');
							if(\get_class($args[$arg_pos]) != __NAMESPACE__.'\byref') throw new \Exception('Error: byref value = false type.');
							$byref[$id] = $this->byref_var($args[$arg_pos]->value);
						}
					}
				}
				
				$varname = $this->nvn();
				$resptype = new resp($varname, $this->asc->aw ? false : true);

				$this->asc->wtp('$args = '.$this->otp($args).';');
				foreach($byref as $id => $var) $this->asc->wtp('$args['.$id.'] = &$'.$var.';');	
				
				foreach($args as $id => $arg)
				{
					if($this->is_asi($arg))
					{
						$this->asc->wtp('$args['.$id.'] = $'.$arg->vari_name.';');	
					}
					else
					{
						if(!$this->useable_var($arg) && !isset($byref[$id]))
						{
							throw new \Exception('Error: passing Instanced Object to async Instance not supported!');
						}
					}
				}
				
				$this->asc->wtp('$method = '.$this->otp($method).';');
				$this->asc->wtp('$'.$varname.' = call_user_func_array(array($'.$this->vari->varname.', $method), $args);');
				$this->asc->rfp(null); // Catch Exception if thrown
				$this->asc->wtp('echo '.__NAMESPACE__.'\core::sdo(gettype(@$'.$varname.')).PHP_EOL;', $resptype);

				$this->parse_me($me, $resptype, $varname);
				return $me;
			}
			else
			{
				throw new \Exception($method . " not Found as Child of " . $this->ob_t);
			}
		}
		
		private function parse_me(&$me, &$resptype, $varname)
		{
			if(!$resptype->ready && !$this->asc->aw)
			{
				$me = new ghost($this->asc, $resptype, $me);
				return;
			}
			if($resptype->value == "object")
			{
				$respc = new resp();
				$this->asc->wtp('echo '.__NAMESPACE__.'\core::sdo(get_class($'.$varname.')).PHP_EOL;', $respc);
				$reflcore = new refl_core($respc->value);
				$cl = $this->wrap_cls($respc->value, $reflcore);
				$me = new $cl($this->asc, $resptype, $me, $reflcore);
			}
			elseif($resptype->value == "array")
			{
				$resp = new resp();
				$this->asc->wtp('echo '.__NAMESPACE__.'\core::sdo($'.$varname.').PHP_EOL;', $resp);
				$me = $resp->value;
			}
			elseif($resptype->value == "NULL")
			{
				$me = null;
			}
			else
			{
				$resp = new resp();
				$this->asc->wtp('echo '.__NAMESPACE__.'\core::sdo($'.$varname.').PHP_EOL;', $resp);
				$data = $resp->value;
				\settype($data, $resptype->value);
				$me = $data;
			}
			return;
		}
		
		private function byref_var(&$var)
		{
			$varname = core::nvn();
			$resp = new resp();
			$resp->__bind_byref($var);
			$this->asc->wtp('$'.$varname.' = '.$this->otp($var->read).';'); // Copy in
			$this->asc->wtp('$ref_a[] = new '.__NAMESPACE__.'\byref_core($'.$varname.',$trans_id);', $resp, false); // Copy out
			return $varname;	
		}
		
		private function reg_async_var(&$var, $name)
		{
			$this->myvars[$name] = new var_core($var, $name);
		}
		
		private function chk()
		{
			if(!$this->vari->ready)
				throw new \Exception('Object not ready!');
			elseif($this->vari->value != 'object')
				throw new \Exception('I\'m not a object. You can\'t call methods on me.');
				
			if(\is_null($this->ob_t))
			{
				$respc = new resp();
				$this->asc->wtp('echo '.__NAMESPACE__.'\core::sdo(get_class($'.$this->vari->varname.')).PHP_EOL;', $respc);
				$this->ob_t = new refl_core($respc->value);
			}
		}
		
		public function __wait(&$me)
		{
			if(\gettype($this->me) == "object")
			{
				if($this->vari->value != "object" || \get_class($this->me) == __NAMESPACE__.'\ghost')
				{
					$this->parse_me($this->me, $this->vari, $this->vari->varname);
					$me = $this->me;		
				}	
			}
		}
		
		public function __ready(&$me)
		{
			if($this->vari->ready)
				$this->__wait($me);
			return $this->vari->ready;
		}
		
		public function __autowait($state)
		{
			$this->asc->aw = $state;
		}
		
		public function __inuse()
		{
			$this->asc->rfp(null); // Try Read Standing and/or Exception
			return (BOOL)$this->asc->get_open_reads();
		}
		
		function &__get($name)
		{
			if($name == "vari_name")
			{
				$d = $this->vari->varname;
				return $d;
			}
			$this->chk();
			if(isset($this->myvars[$name]))
			{
				if ($this->is_asi($this->myvars[$name])) return $this->myvars[$name];
			}
			$resp = new resp();
			//\var_dump($this->vari->varname, $name);
			$this->asc->wtp('echo '.__NAMESPACE__.'\core::sdo(isset($'.$this->vari->varname.'->'.$name.') ? $'.$this->vari->varname.'->'.$name.' : null).PHP_EOL;', $resp);
			$this->asc->rfp(null); // Catch Exception if thrown
			if($resp->ready || $this->asc->aw)
			{
				$ret = $resp->value;
				return $ret;
			}
			else
			{
				$me = new ghost($this->asc, $resp, $me);
				return $me;
			}
		}
		
		public function __close()
		{
			return $this->asc->destruct();
		}
		
		function __set($name, $value)
		{
			if(\gettype($value) == 'object')
			{
				if($this->is_asi($value))
				{
					$this->myvars[$name] = $value;
					$this->asc->wtp('$'.$this->vari->varname.'->'.$name.' = @$'.$value->vari_name.';');
					$this->asc->rfp(null); // Catch Exception if thrown
				}
				elseif(\explode("\\", \get_class($value))[0] == __NAMESPACE__)
				{
					if(\get_class($value) == __NAMESPACE__.'\\method')
					{
						$resptype = new resp($this->vari->varname.'->'.$name, $this->asc->aw ? false : true);
						$exec_vars = $value->vars;
						$n_exec_vars = array();
						foreach($exec_vars as $d_var)
						{
							if($this->is_asi($d_var))
								$n_exec_vars[] = '$'.$d_var->vari_name;
							else
							{
								if($this->useable_var($d_var))
								{
									$n_exec_vars[] = $this->otp($d_var);
								}
								else
								{
									throw new \Exception('Error: passing Instanced Object to async Instance not supported!');
								}
							}
						}
						if(\count($n_exec_vars) > 0)
							$this->asc->wtp('$'.$this->vari->varname.'->'.$name.' = call_user_func("'.$value->method.'", '.\implode(",", $n_exec_vars).');');
						else
							$this->asc->wtp('$'.$this->vari->varname.'->'.$name.' = call_user_func("'.$value->method.'");');
						$this->asc->rfp(null); // Catch Exception if thrown
						$this->asc->wtp('echo '.__NAMESPACE__.'\core::sdo(gettype($'.$this->vari->varname.'->'.$name.')).PHP_EOL;', $resptype); 
						$this->parse_me($me, $resptype, $this->vari->varname.'->'.$name);
						$this->myvars[$name] = $me;
					}
					else
					{
						$resptype = new resp($this->vari->varname.'->'.$name, $this->asc->aw ? false : true);
						$this->asc->wtp('$'.$this->vari->varname.'->'.$name.' = new '.\explode("\\", \get_class($value))[1].'(...'.$this->otp($value->__asi_callback()).');');
						$this->asc->rfp(null); // Catch Exception if thrown
						$this->asc->wtp('echo '.__NAMESPACE__.'\core::sdo(gettype($'.$this->vari->varname.'->'.$name.')).PHP_EOL;', $resptype); 
						$reflcore = new refl_core(\explode("\\", \get_class($value))[1]);
						$cl = $this->wrap_cls(\explode("\\", \get_class($value))[1], $reflcore);
						$me = new $cl($this->asc, $resptype, $me, $reflcore);
						$this->myvars[$name] = $me;
						if($this->asc->aw) $t = $resptype->value;
					}
				}
				else
				{
					if($this->useable_var($value))
					{
						$resptype = new resp($this->vari->varname.'->'.$name, $this->asc->aw ? false : true);
						$this->asc->wtp('$'.$this->vari->varname.'->'.$name.' = '.$this->otp($value).';');
						$this->asc->rfp(null); // Catch Exception if thrown
						$this->asc->wtp('echo '.__NAMESPACE__.'\core::sdo(gettype($'.$this->vari->varname.'->'.$name.')).PHP_EOL;', $resptype); 
						$reflcore = new refl_core(\get_class($value));
						$cl = $this->wrap_cls(\get_class($value), $reflcore);
						$me = new $cl($this->asc, $resptype, $me, $reflcore);
						$this->myvars[$name] = $me;
						if($this->asc->aw) $t = $resptype->value;
					}
					else
					{
						throw new \Exception('Error: passing Instanced Object to async Instance not supported!');
					}
				}
			}
			elseif(\gettype($value) == 'resource')
			{
				throw new \Exception('Error: passing resource Object to async Instance not supported!');
			}
			else
			{
				$this->asc->wtp('$'.$this->vari->varname.'->'.$name.' = '.$this->otp($value).';');
				$this->asc->rfp(null); // Catch Exception if thrown
			}
		}
	}

	class resp
	{
		private $value;
		private $ready;
		private $asc;
		private $varname;
		private $trans_id;
		private $aw_reset;
		
		public function __construct($varname = null, $aw_res = false)
		{
			$this->value = null;
			$this->ready = false;
			$this->asc = null;
			$this->varname = $varname;
			$this->aw_reset = $aw_res;
		}
		
		public function __get($name)
		{
			if(!$this->ready && $name == 'value')
			{
				$this->asc->rfp(-1, $this->trans_id); // Blocking @ this point
				if(!$this->ready) throw new \Exception('Critical Pipe Error! Child Process probably died!');
			}
			elseif(!$this->ready && $name == 'ready')
				$this->asc->rfp(1); // try re-read
			if($this->ready && $this->aw_reset) $this->asc->aw = true;
			return $this->$name;
		}
		
		public function __set($name, $val)
		{
			if($name == 'value')
			{
				$this->value = $val;
				$this->ready = true;
			}
		}
		
		public function __set_asc(&$asc)
		{
			$this->asc = &$asc;
		}
		
		public function __bind_byref(&$var)
		{
			$this->value = &$var;
		}
		
		public function __set_tid($tid)
		{
			$this->trans_id = $tid;
		}
	}
	
	class async_object extends ghost
	{
		function __construct(&$me, $instance_name, $class, $additional_includes)
		{
			$this->refres_code();
			$interface = new pipe_interface($instance_name, $class, $additional_includes);
			$type = $interface->get_init_type();
			$cls = $interface->get_init_cls();	
			$reflcore = new refl_core($cls->value);
			$cl = $this->wrap_cls($cls->value, $reflcore);
			$me = new $cl($interface, $type, $me, $reflcore);
			\register_shutdown_function([$me, '__close']);
		}
		
		private function refres_code()
		{
			$clss = \get_declared_classes();
			foreach($clss as $cls)
			{
				$x = new refl_core($cls);
				if($x->isExtendable($cls) && !\class_exists(__NAMESPACE__.'\\'.$cls))
				{
					eval('namespace '.__NAMESPACE__.' {
						class '.$cls.' extends \\'.$cls.'
						{private $construct_data;
						public function __construct(...$data)
						{ $this->construct_data = $data;}
						public function __asi_callback()
						{ return $this->construct_data;}}}');
				}
			}
			$methods = \get_defined_functions();
			foreach($methods["internal"] as $methode)
			{
				$this->cpf($methode);
			}
			foreach($methods["user"] as $methode)
			{
				$this->cpf($methode);
			}
		}
		
		private function cpf($func)
		{
			$x = new \ReflectionFunction($func);
			if($x->inNamespace() === false && !function_exists(__NAMESPACE__.'\\'.$func) && $func != "assert")
			{
				eval('namespace '.__NAMESPACE__.' {
				function '.$func.'(...$data)
				{return new method("'.$func.'", $data);}}');
			}
		}
	}
	
	class pipe_interface extends core
	{
		public $aw;
		private $debug = false;
		private $timeout = 5; // 5 x 200µs = 1ms
		private $process;
		private $pipe;
		private $trans_id;
		private $read_log;
		private $open_read;
		private $initial_resp;
		private $initial_cl;
		private $ex;
		private $base = 'zoyMzg6InBocCAtciAiXCRyZWZfYT1hc'.
						'nJheSgpO1wkZWNudD0wO3doaWxlKDEpe'.
						'1wkY21kPXJlYWRsaW5lKCk7aWYoXCRjb'.
						'WQ9PScnKXtcJGVjbnQrKztpZihcJGVjb'.
						'nQ+MilleGl0KDApO31lbHNle1wkZWNud'.
						'D0wO3RyeXtldmFsKFwkY21kKTt9Y2F0Y'.
						'2goRXhjZXB0aW9uIFwkZSl7ZWNobyAnI'.
						'zAjOicuYXNpXFxjb3JlOjpzZG8oXCRlK'.
						'S5QSFBfRU9MO31mb3JlYWNoKFwkcmVmX'.
						'2EgYXMgXCRyZWYpe1wkcmVmLT5jaygpO'.
						'319fSIiOw==';
		
		function __construct($instance_name, $class, $additional_includes)
		{
			if(\gettype($class) == 'object')
			{
				if(!\class_exists(\get_class($class))) throw new \Exception('$class Variable is not a Class Instance');
			}
			elseif(\gettype($class) == 'array')
			{
				if(!\class_exists($class[0])) throw new \Exception('$class Variable is not a Class Name');
			}
			else
			{
				if(!\class_exists($class)) throw new \Exception('$class Variable is not a Class Name');
			}
			
			$descriptorspec = array(0 => array("pipe", "r"),1 => array("pipe", "w"),2 => array("pipe", "w"));
			$this->aw = true;
			
			$cwd = $_SERVER['DOCUMENT_ROOT'];
			$bas = $this->usd(\chr(99).$this->base);
			if(\gettype($class) == 'array')
				$arc = new refl_core($class[0]);
			else
				$arc = new refl_core($class);
			$env = array();
			$inc = array(__FILE__);
			if($_SERVER["SCRIPT_FILENAME"] != $arc->getFileName() && $arc->isUserDefined()) $inc[] = $arc->getFileName();
			if(!\is_null($additional_includes))
			{
				foreach($additional_includes as $path) $inc[] = $path;
			}
			$env['asc_include'] = $this->sdo($inc);
			$env['asc_class'] = $this->sdo($class);
			$this->base = null;
			
			$this->process = \proc_open(": '".$instance_name."' && ".$bas, $descriptorspec, $this->pipe, $cwd, $env);
			
			if (\is_resource($this->process))
			{
				\stream_set_blocking($this->pipe[0], false);
				\stream_set_blocking($this->pipe[1], false);
				\stream_set_blocking($this->pipe[2], false);
				
				$this->trans_id = -1;
				$this->read_log = array();
				$this->open_read = array();
				
				if($this->debug)
				{
					$this->wtp('ini_set("display_errors", 1);');
					$this->wtp('ini_set("display_startup_errors", 1);');
					$this->wtp('error_reporting(E_ALL);');
				}
				
				$varname = 'asi_main_obj'; //$this->nvn();
				$resptype = new resp($varname);
				$cltype = new resp();
				
				$exresp = new resp();
				$exresp->__bind_byref($this->ex);				
				$this->wtp('foreach (unserialize(base64_decode($_SERVER["asc_include"])) AS $File) include_once($File);', $exresp, false);
				if(\gettype($class) == 'object')
				{
					$this->wtp('$'.$varname.' = '.__NAMESPACE__.'\core::usd($_SERVER["asc_class"]);');
				}
				elseif(\gettype($class) == 'array')
				{
					$this->wtp('$mainargs = '.__NAMESPACE__.'\core::usd($_SERVER["asc_class"]);');
					$this->wtp('$main=array_shift($mainargs);');
					$this->wtp('if(count($mainargs)==0){$'.$varname.' = new $main();}else{$'.$varname.' = new $main(...$mainargs);}');
				}
				else
				{
					$this->wtp('$main = '.__NAMESPACE__.'\core::usd($_SERVER["asc_class"]);');
					$this->wtp('$'.$varname.' = new $main();');
				}
				$this->wtp('echo '.__NAMESPACE__.'\core::sdo(gettype($'.$varname.')).PHP_EOL;', $resptype);
				$this->wtp('echo '.__NAMESPACE__.'\core::sdo(get_class($'.$varname.')).PHP_EOL;', $cltype); 
					
				$this->initial_resp = &$resptype;
				$this->initial_cl = &$cltype;
			}
			else
			{
				throw new \Exception('Error while creating Async Instance');
			}
		}
		
		public function &get_init_type()
		{
			return $this->initial_resp;
		}
		
		public function &get_init_cls()
		{
			return $this->initial_cl;
		}
		
		public function get_open_reads()
		{
			return \count($this->open_read) -1;
		}
		
		//write to process
		public function wtp($data, &$resp_var = false, $etid = true)
		{
			if($resp_var !== false) 
			{
				\fwrite($this->pipe[0], '$trans_id = '.++$this->trans_id.';');
				$resp_var->__set_asc($this);
				$resp_var->__set_tid($this->trans_id);
				$this->read_log[$this->trans_id] = &$resp_var;
				$this->open_read[] = $this->trans_id;
				if($etid)
					$retval = \fwrite($this->pipe[0], 'echo "#".$trans_id."#:";'.$data . PHP_EOL);
				else
					$retval = \fwrite($this->pipe[0], $data . PHP_EOL);
			}
			else
				$retval = \fwrite($this->pipe[0], $data . PHP_EOL);
			
			if($resp_var !== false && $etid) 
			{
				$this->rfp(null);
			}
			return $retval;
		}
		
		//read from process
		public function rfp($timeout, $tid = null)
		{
			$data = "";
			if(\is_null($timeout)) $timeout = $this->timeout;
			if(\count($this->open_read)>0)
			{
				if($timeout == -1 && !\is_null($tid))
				{
					if(\in_array($tid, $this->open_read))
					{
						\stream_set_blocking($this->pipe[1], true);
						$data = \stream_get_line($this->pipe[1],0,PHP_EOL);
						\stream_set_blocking($this->pipe[1], false);
					}
				}
				if (\strlen($data) == 0)
				{
					$data = \stream_get_line($this->pipe[1],0,PHP_EOL);
					$i = 0;
					do
					{
						if (\strlen($data) > 0) break;
						\usleep(200);
						$data = \stream_get_line($this->pipe[1],0,PHP_EOL);
						if (\strlen($data) > 0) break;
						$i++;
					} while ($i < $timeout);
				}
				if (\strlen($data) > 0)
				{
					$this->wrl($data);
					if(\count($this->open_read)>0)
						$this->rfp($timeout, $tid);
				}
			}
		}
		
		private function wrl($data)
		{
			if(\preg_match_all('/(#([0-9]+)#:)([A-Za-z0-9\/+=]*)/', $data, $matches, PREG_SET_ORDER))
			{
				foreach($matches as $match)
				{
					$trans_id = $match[2];
					$data = $this->usd($match[3]);
					if (($key = \array_search($trans_id, $this->open_read)) !== false && $trans_id != 0) unset($this->open_read[$key]);
					$this->read_log[$trans_id]->value = $data;
					if($trans_id == 0) throw $data; // Override-Exception
				}
			}
			else
				\trigger_error("ASI-Stream::Unexpected-Error: ".$data, E_USER_WARNING); // Unexpected data?!
			return;
		}
		
		private function kill_instance()
		{
			$this->wtp('exit(0);');
			
			\fclose($this->pipe[0]);
			
			$final_status = null;
			do
			{
				\usleep(1000);
				$data = \stream_get_contents($this->pipe[1]);
				if($data != "")
				{
					//\trigger_error("ASI-Stream::Pending: ".$data, E_USER_WARNING); // probably Expected, but unused data?!
				}
				$final_status = \proc_get_status($this->process);
			} while ($final_status["running"]);
			
			\fclose($this->pipe[1]);	
			\fclose($this->pipe[2]);	
			
			\proc_close($this->process);
			//\trigger_error("ASI CLEANUP SUCCESSFULL!", E_USER_NOTICE);
			return $final_status['exitcode'];
		}
		
		public function destruct()
		{
			if(\is_resource($this->process))
			{
				return $this->kill_instance();
			}
			return null;
		}
		
		function __destruct()
		{
			if(\is_resource($this->process))
			{
				$final_status = $this->kill_instance();
				if($final_status != 0)
				\trigger_error("ExitCode: " . $final_status, E_USER_WARNING);
			}
		}
	}
}
?>
