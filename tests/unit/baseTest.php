<?php

class baseTest extends \Codeception\TestCase\Test
{
   /**
    * @var \CodeGuy
    */
    protected $codeGuy;
    protected $V;

    protected function _before()
    {
        $this->V = new Verified();
        parent::_before();
    }

    protected function _after()
    {
        parent::_after();
    }

    protected function console($val)
    {
        fwrite(STDERR, print_r($val, TRUE));
    }

}