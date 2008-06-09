<?php
/**
 * This file is part of phpUnderControl.
 * 
 * PHP Version 5.2.0
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
 * @category  QualityAssurance
 * @package   SourceBrowser
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

/**
 * This class provides a simple log file merger.
 *
 * @category  QualityAssurance
 * @package   SourceBrowser
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucLogMerger
{
    /**
     * The log directory.
     *
     * @type string
     * @var string $logDir
     */
    private $logDir = null;
    
    /**
     * Constructs a new log file merger instance.
     *
     * @param string $logDir The log directory.
     * 
     * @throws phpucErrorException 
     *         If the given log directory doesn't exist.
     */
    public function __construct( $logDir )
    {
        $this->logDir = $logDir;
        
        if ( is_dir( $logDir ) === false )
        {
            throw new phpucErrorException( "Invalid log directory '{$logDir}'." );
        }
    }
    
    /**
     * Collects all xml log files in the given <b>$logDir</b> and merges them
     * in a single log file.
     *
     * @param string $outputFile The output file.
     * 
     * @return DOMDocument
     */
    public function mergeFiles( $outputFile )
    {
        $merged  = new DOMDocument( '1.0', 'UTF-8' );
        $element = $merged->createElement( 'phpundercontrol' );
        
        $merged->preserveWhiteSpace = false;
        $merged->formatOutput       = true;
        
        foreach ( glob( "{$this->logDir}/*.xml" ) as $file )
        {
            $log                     = new DOMDocument( '1.0', 'UTF-8' );
            $log->preserveWhiteSpace = false;
            $log->load( $file );
            
            $imported = $merged->importNode( $log->documentElement, true );
            $element->appendChild( $imported );
        }
        
        $merged->appendChild( $element );
        $merged->save( $outputFile );
        
        return $merged;
    }
}