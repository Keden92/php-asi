<?php

function test_function_1($int)
{
	// Do what ever you want here
	sleep($int);
	return "sleep succeed"; // Return-Type must conform Serializable interface!!
}


class test_class implements Serializable
{
	private $x = null;
	
	public function __construct($x)
	{
		$this->x = $x;
	}
	
	public function serialize()
	{
        return serialize($this->x);
    }
    
    public function unserialize($data)
    {
        $this->x = unserialize($data);
    }
	
	public function mysleep()
	{
		sleep($this->x);
		return $this->x;
	}
	
	/* throw exception */
	public function exception_example()
	{
		throw new Exception("test error");
	}
	
	/* throw exception after some time */
	public function exception_example_2()
	{
		sleep(1); 
		throw new Exception("test error");
	}
}

?>
