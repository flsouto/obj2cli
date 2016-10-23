<?php

function obj2cli($object){

	if(!is_object($object)){
		throw new \InvalidArgumentException("obj2cli expects an object!");
	}

	$stdin = fopen("php://stdin","r");

	$show_usage = function($method) use($object){
		$reflect = new ReflectionMethod($object, $method);
		$params = [];
		foreach($reflect->getParameters() as $param){
			if(!$param->isOptional()){
				$params[] = $param->getName();
			} else {
				$params[] = '['.$param->getName().' = '.$param->getDefaultValue().']';
			}
		}
		if(empty($params)){
			echo "$method (no parameters)";
		} else {
			echo "$method ".implode(" ", $params);
		}
		echo "\n";
	};

	while(true){

		if(method_exists($object,'getObj2cliName')){
			echo $object->getObj2cliName().'> ';
		} else {
			echo get_class($object).'> ';
		}

		$cmd = trim(fgets($stdin));
		$args = explode(" ",$cmd);
		$func = array_shift($args);

		if(empty($func)){
			continue;
		}

		if(!method_exists($object, $func)){
	
			if($func=='help'){
				$methods = get_class_methods($object);
				foreach($methods as $method){
					if(substr($method,0,1)=='_'){
						continue;
					}
					echo '- ';
					$show_usage($method);
				}
				continue;
			} else if($func=='exit') {
				break;
			} else {
				echo "Command not recognized: $func\n";
				continue;
			}
		}

		if(!empty($args[0])&&$args[0]=='--help'){
			$show_usage($func);
			continue;
		}

		$reflect = new ReflectionMethod($object, $func);
		$reargs = array();
		foreach($reflect->getParameters() as $param){
			$arg = '__UNDEFINED__';
			if(!empty($args)){
				$arg = array_shift($args);
			}
			if(strtolower($arg)=='null'){
				$arg = null;
			}
			if(!$param->isOptional() && $arg=='__UNDEFINED__'){
				printf("Parameter %d to '$func' is mandatory. Use '$func --help' for more usage info. \n", $param->getPosition()+1);
				continue 2;
			}
			$reargs[] = $arg=='__UNDEFINED__' ? $param->getDefaultVaue() : $arg;
		}

		ob_start();
		$return = call_user_func_array([$object,$func], $reargs);
		$output = ob_get_clean();

		echo rtrim($output,"\n")."\n";

		if(is_object($return)){
			obj2cli($return); // why not?
		}

	}

}