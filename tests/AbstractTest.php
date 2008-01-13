<?php
/**
 * This file is part of phpUnderControl.
 *
 * Copyright (c) 2007-2008, Manuel Pichler <mapi@manuel-pichler.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

define( 'PHPUC_TEST', true );
define( 'PHPUC_TEST_DIR', dirname( __FILE__ ) . '/run' );

if ( strpos( '@php_dir@', '@php_dir' ) === false )
{
    define( 'PHPUC_SOURCE', '@php_dir@/phpUnderControl' );
}
else
{
    define( 'PHPUC_SOURCE', realpath( dirname( __FILE__ ) . '/../src' ) );
}


require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Abstract base class for phpUnderControl test cases.
 *
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
abstract class phpucAbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * Is the current operation system Windows?
     *
     * @type boolean
     * @var boolean $windows
     */
    public static $windows = false;

    /**
     * Removes all test contents.
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->clearTestContents();
        
        parent::tearDown();
    }
    
    /**
     * Prepares the global <b>$argv</b> array.
     *
     * @param array $argv A new argument array.
     */
    protected function prepareArgv( array $argv = null )
    {
        if ( $argv === null )
        {
            unset( $GLOBALS['argv'] );
        }
        else
        {
            // Add dummy file
            array_unshift( $argv, 'phpuc.php' );
            // Set new $argv array
            $GLOBALS['argv'] = $argv;
        }
    }
    
    /**
     * Creates a directory structure under the test directory.
     *
     * @param array $directories Test directories.
     * 
     * @return array(string)
     */
    protected function createTestDirectories( array $directories )
    {
        $fullPaths = array();
        
        foreach ( $directories as $directory )
        {
            // Create full testing path
            $fullPath = PHPUC_TEST_DIR . '/' . $directory;
            
            mkdir( $fullPath, 0755, true );
            
            $fullPaths[] = $fullPath;
        }
        return $fullPaths;
    }
    
    /**
     * Creates a single test file.
     *
     * @param string $filePath The test filepath.
     * @param string $content  Optional file contents.
     * 
     * @return string
     */
    protected function createTestFile( $filePath, $content = '...' )
    {
        $fullPath = PHPUC_TEST_DIR . '/' . $filePath;
        
        file_put_contents( $fullPath, $content );
        
        chmod( $fullPath, 0755 );
        
        return $fullPath;
    }
    
    protected function clearTestContents( $directory = null )
    {
        if ( $directory === null )
        {
            $directory = PHPUC_TEST_DIR;
        }
        
        $it = new DirectoryIterator( $directory );
        foreach ( $it as $entry )
        {
            if ( $entry->isDot() )
            {
                continue;
            }
            else if ( $entry->isDir() && $entry->getFilename() !== '.svn' )
            {
                $this->clearTestContents( $entry->getPathname() );
                rmdir( $entry->getPathname() );
            } 
            else if ( $entry->isFile() )
            {
                unlink( $entry->getPathname() );
            }
        }
    }
    
    /**
     * Initializes the test environment.
     *
     * @return void
     */
    public static function init()
    {
        // Load phpUnderControl base class
        require_once PHPUC_SOURCE . '/PhpUnderControl.php';
        
        // Register autoload
        spl_autoload_register( array( 'phpucPhpUnderControl', 'autoload' ) );
        
        // Load ezcBase class
        require_once 'ezc/Base/base.php';
        
        spl_autoload_register( array( 'ezcBase', 'autoload' ) );
        
        phpucConsoleOutput::set( new phpucConsoleOutput() );
        
        PHPUnit_Util_Filter::addDirectoryToWhitelist( PHPUC_SOURCE );
        
        if ( !is_dir( PHPUC_TEST_DIR) )
        {
            mkdir( PHPUC_TEST_DIR );
        }
        
        self::$windows = ( stripos( PHP_OS, 'WIN' ) !== false );
    }
}

phpucAbstractTest::init();