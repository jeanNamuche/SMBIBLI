<?php
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function testStrClean()
    {
        // Incluir el archivo que contiene la función a probar
        require_once 'Config/Helpers.php';

        // Probar que la función limpia espacios extra
        $this->assertEquals('hola mundo', strClean('  hola   mundo  '));
    }

    public function testStrCleanRemovesSQLInjection()
    {
        // Incluir el archivo que contiene la función a probar
        require_once 'Config/Helpers.php';
        
        // Probar que la función elimina palabras clave de inyección SQL
        $this->assertEquals(' users WHERE id = 1 ', strClean("SELECT * FROM users WHERE id = 1 OR '1'='1'"));
    }

    public function testStrCleanRemovesScriptTags()
    {
        require_once 'Config/Helpers.php';
        $this->assertEquals('alerta("XSS");', strClean('<script>alerta("XSS");</script>'));
    }

    public function testStrCleanRemovesDoubleHyphens()
    {
        require_once 'Config/Helpers.php';
        $this->assertEquals('no', strClean('no--'));
    }

    public function testStrCleanRemovesSquareBrackets()
    {
        require_once 'Config/Helpers.php';
        $this->assertEquals('select', strClean('[select]'));
    }
}