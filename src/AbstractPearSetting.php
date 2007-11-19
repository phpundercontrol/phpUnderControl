<?php
/**
 * This file is part of phpUnderControl.
 *
 * Copyright (c) 2007, Manuel Pichler <mapi@manuel-pichler.de>.
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
 * @package phpUnderControl
 */

/**
 * Abstract base class for the PEAR based options.
 *
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   $Id$
 * 
 * @property      string $cliTool        The PEAR cli command line tool.
 * @property      string $pearInstallDir An optional PEAR install directory.
 * @property      string $outputDir      An optional output directory.
 * @property-read string $fileName       The full command file name.
 */
abstract class pucAbstractPearSetting extends pucAbstractSetting
{
    /**
     * The ctor takes the cli script name as argument and the PEAR install dir 
     * as an optional argument.
     *
     * @param string $cliTool        The PEAR cli tool.
     * @param string $pearInstallDir PEAR install dir.
     * @param string $outputDir      An output dir for the generated contents.
     */
    public function __construct( $cliTool, $pearInstallDir = null, $outputDir = null )
    {
        $this->properties['cliTool']        = null;
        $this->properties['outputDir']      = null;
        $this->properties['pearInstallDir'] = null;
        
        $this->cliTool        = $cliTool;
        $this->outputDir      = $outputDir;
        $this->pearInstallDir = $pearInstallDir;
    }
    
    /**
     * Does the primary validation that the command line tool exists. If the
     * tool exists this method passes the request to the internal template 
     * method {@link doValidate()}.
     *
     * @return void
     */
    public final function validate()
    {
        // Get possible or configured pear path
        if ( $this->pearInstallDir === null )
        {
            $paths = explode( PATH_SEPARATOR, getenv( 'PATH' ) );
        }
        else
        {
            $paths = array( $this->pearInstallDir );
        }
        $paths = array_unique( $paths );

        foreach ( $paths as $path )
        {
            $fileName = sprintf( '%s/%s', $path, $this->cliTool );
            
            if ( file_exists( $fileName ) === false )
            {
                continue;
            }
            if ( is_executable( $fileName ) === false )
            {
                continue;
            }
            $this->properties['fileName'] = $fileName;
            break;
        }
        if ( $this->fileName === null )
        {
            printf(
                'Missing command line tool "%s". Please check your PATH settings.%s',
                $this->cliTool,
                PHP_EOL
            );
            exit( 1 );
        }
        else if ( $this->pearInstallDir === null )
        {
            $dir = dirname( $this->fileName );
            if ( strpos( getenv( 'PATH' ), $dir ) !== false )
            {
                $this->properties['fileName'] = $this->cliTool;
            }
        }

        // Check output directory
        if ( $this->outputDir !== null && is_dir( $this->outputDir ) === false )
        {
            printf(
                'The output directory "%s" doesn\'t exist.%s',
                $this->outputDir,
                PHP_EOL
            );
            exit( 1 );
        }
        
        $this->doValidate();
    }
    
    /**
     * Magic property setter method.
     *
     * @param string $name  The property name.
     * @param mixed  $value The property value.
     * 
     * @return void
     * @throws OutOfRangeException If the property doesn't exist or is readonly.
     */
    public function __set( $name, $value )
    {
        switch ( $name )
        {
            case 'cliTool':
                $this->properties[$name] = $value;
                break;
                
            case 'outputDir':
                if ( trim( $value ) === '' )
                {
                    $value = sys_get_temp_dir() . '/php-under-control';
                    if ( file_exists( $value ) === false )
                    {
                        mkdir( $value );
                    }
                }
                $regex = sprintf( '#%s+$#', DIRECTORY_SEPARATOR );
                $this->properties[$name] = preg_replace( $regex, '', $value );
                break;
                
            case 'pearInstallDir':
                if ( trim( $value) === '' )
                {
                    $this->properties[$name] = null;
                }
                else
                {
                    $regex = sprintf( '#%s+$#', DIRECTORY_SEPARATOR );
                    $this->properties[$name] = preg_replace( $regex, '', $value );
                }
                break;
                
            default:
                throw new OutOfRangeException(
                    sprintf( 'Unknown or readonly property $%s.', $name )
                );
                break;
        }
    }
    
    /**
     * Template validate method for additional checks.
     *
     * @return void
     */
    protected function doValidate()
    {
        // Nothing todo hear
    }
}