<?php

namespace App\FileManager;

class FileManager
{    
    protected $serviceManager;    
    
    protected $chmodFile = '0666';
    
    protected $chmodDir = '0777';
    
    protected $createGroup = false;
    
    public function __construct($options)
    {  
        $this->setOptions($options);    
    }
    
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }
    
    public function setServiceManager($sm)
    {
        $this->serviceManager = $sm;
        return $this;
    }
    
    public function setChmodFile($chmod)
    {
        $this->chmodFile = $chmod;
        return $this;
    }
    
    public function setChmodDir($chmod)
    {
        $this->chmodDir = $chmod;
        return $this;
    }
    
    public function setCreateGroup($group)
    {
        $this->createGroup = $group;
        return $this;
    }

    public function getDirs($path)
    {
        $dirs = array();
        if (is_dir($path)) {
            $dir = scandir($path);
            foreach ($dir as $entry) {
                if (is_dir($path . '/' . $entry) && $entry != '..' && $entry != '.') {
                    $dirs[] = $entry;
                }
            }
        } else {
            throw new \Exception($path . ' is not a dir');
        }
        return $dirs;
    }
    
    /**
     * Wrapper function for mkdir.
     *
     * @param	string		Absolute path to folder, see PHP mkdir() function. Removes trailing slash internally.
     * @return	boolean		TRUE if @mkdir went well!
     */
    public function mkdir($newFolder, $recursive = false)
    {        
        return mkdir($newFolder, octdec($this->chmodDir), $recursive);        
    }
    
    public function fixPermissions($path, $recursive = FALSE)
    {
        if (SERVER_OS != 'WIN') {
            $result = FALSE;
            
            if (!$this->isAbsPath($path)) {
				$path = $this->getFileAbsFileName($path, FALSE);
			}
            
            if ($this->isAllowedAbsPath($path)) {
                if (is_file($path)) {
                    // "@" is there because file is not necessarily OWNED by the user
                    $result = @chmod($path, octdec($this->chmodFile));
                } elseif (is_dir($path)) {
                    $path = preg_replace('|/$|', '', $path);
                    // "@" is there because file is not necessarily OWNED by the user
                    $result = @chmod($path, octdec($this->chmodDir));
                }

                // Set createGroup if not empty
      			if ($this->createGroup) {
						// "@" is there because file is not necessarily OWNED by the user
					$changeGroupResult = @chgrp($path, $this->createGroup);
					$result = $changeGroupResult ? $result : FALSE;
				}
                // Call recursive if recursive flag if set and $path is directory
                if ($recursive && is_dir($path)) {
                    $handle = opendir($path);
                    while (($file = readdir($handle)) !== FALSE) {
                        unset($recursionResult);
                        if ($file !== '.' && $file !== '..') {
                            if (is_file($path . '/' . $file)) {
                                $recursionResult = $this->fixPermissions($path . '/' . $file);
                            } elseif (is_dir($path . '/' . $file)) {
                                $recursionResult = $this->fixPermissions($path . '/' . $file, TRUE);
                            }
                            if (isset($recursionResult) && !$recursionResult) {
                                $result = FALSE;
                            }
                        }
                    }
                    closedir($handle);
                }
            }
        } else {
            $result = TRUE;
        }
        return $result;
    }
    
    /**
	 * Returns the absolute filename of a relative reference, resolves the "EXT:" prefix (way of referring to files inside extensions) and checks that the file is inside the PATH_site of the TYPO3 installation and implies a check with \TYPO3\CMS\Core\Utility\GeneralUtility::validPathStr(). Returns FALSE if checks failed. Does not check if the file exists.
	 *
	 * @param string $filename The input filename/filepath to evaluate
	 * @param boolean $onlyRelative If $onlyRelative is set (which it is by default), then only return values relative to the current PATH_site is accepted.
	 * @return string Returns the absolute filename of $filename IF valid, otherwise blank string.
	 */
	static public function getFileAbsFileName($filename, $onlyRelative = TRUE) {
		if (!strcmp($filename, '')) {
			return '';
		}
		
        $relPathPrefix = ROOT_PATH;
            
		if (!$this->isAbsPath($filename)) {
			// relative. Prepended with $relPathPrefix
			$filename = $relPathPrefix . DS . $filename;
		} elseif ($onlyRelative && !$this->isFirstPartOfStr($filename, $relPathPrefix)) {
			// absolute, but set to blank if not allowed
			$filename = '';
		}
		if (strcmp($filename, '') && $this->validPathStr($filename)) {
			// checks backpath.
			return $filename;
		}
	}
    
    /**
     * Returns true if the first part of $str matches the string $partStr
     * Usage: 59
     *
     * @param	string		Full string to check
     * @param	string		Reference string which must be found as the "first part" of the full string
     * @return	boolean		True if $partStr was found to be equal to the first part of $str
     */
    public function isFirstPartOfStr($str, $partStr)
    {
        // Returns true, if the first part of a $str equals $partStr and $partStr is not ''
        $psLen = strlen($partStr);
        if ($psLen) {
            return substr($str, 0, $psLen) == $partStr;
        } else
            return false;
    }

    /**
     * Checks for malicious file paths.
     * Returns true if no '//', '..' or '\' is in the $theFile
     * This should make sure that the path is not pointing 'backwards' and further doesn't contain double/back slashes.
     * So it's compatible with the UNIX style path strings valid for TYPO3 internally.
     * Usage: 14
     *
     * @param	string		Filepath to evaluate
     * @return	boolean		True, if no '//', '\', '/../' is in the $theFile and $theFile doesn't begin with '../'
     * @todo	Possible improvement: Should it rawurldecode the string first to check if any of these characters is encoded ?
     */
    public function validPathStr($theFile)
    {
        if (strpos($theFile, '//') === false && strpos($theFile, '\\') === false && !preg_match('#(?:^\.\.|/\.\./)#', $theFile)) {
            return true;
        }
    }

    /**
     * Checks if the $path is absolute or relative (detecting either '/' or 'x:/' or x:\ as first part of string) and returns true if so.
     * Usage: 8
     *
     * @param	string		Filepath to evaluate
     * @return	boolean
     */
    public function isAbsPath($path)
    {
        // on Windows also a path starting with a drive letter is absolute: X:/
        if (SERVER_OS === 'WIN' && (substr($path, 1, 2) === ':/' || substr($path, 1, 2) === ':\\')) {
            return TRUE;
        }

        // path starting with a / is always absolute, on every system
        return (substr($path, 0, 1) === '/');
    }

    /**
     * Returns true if the path is absolute, without backpath '..' and within the PATH_site OR within the lockRootPath
     * Usage: 5
     *
     * @param	string		Filepath to evaluate
     * @return	boolean
     */
    public function isAllowedAbsPath($path)
    {
        if (
            $this->isAbsPath($path) &&
            $this->validPathStr($path) &&
            $this->isFirstPartOfStr($path, ROOT_PATH)
        ) {
            return true;
        }
    }
    
    /**
     * Recursively gather all files and folders of a path.
     * Usage: 5
     *
     * @param	array		$fileArr: Empty input array (will have files added to it)
     * @param	string		$path: The path to read recursively from (absolute) (include trailing slash!)
     * @param	string		$extList: Comma list of file extensions: Only files with extensions in this list (if applicable) will be selected.
     * @param	boolean		$regDirs: If set, directories are also included in output.
     * @param	integer		$recursivityLevels: The number of levels to dig down...
     * @param string		$excludePattern: regex pattern of files/directories to exclude
     * @return	array		An array with the found files/directories.
     */
    public function getAllFilesAndFoldersInPath(array $fileArr, $path, $extList = '', $regDirs = 0, $recursivityLevels = 99, $excludePattern = '')
    {
        $path = rtrim($path, '/\\');
        if ($regDirs)
            $fileArr[] = $path;
        $fileArr = array_merge($fileArr, $this->getFilesInDir($path, $extList, 1, 1, $excludePattern));

        $dirs = $this->getDirs($path);
        if (is_array($dirs) && $recursivityLevels > 0) {
            foreach ($dirs as $subdirs) {
                if ((string) $subdirs != '' && (!strlen($excludePattern) || !preg_match('/^' . $excludePattern . '$/', $subdirs))) {
                    $fileArr = $this->getAllFilesAndFoldersInPath($fileArr, $path . '/' . $subdirs . '/', $extList, $regDirs, $recursivityLevels - 1, $excludePattern);
                }
            }
        }
        return $fileArr;
    }

    /**
     * Returns an array with the names of files in a specific path
     * Usage: 18
     *
     * @param	string		$path: Is the path to the file
     * @param	string		$extensionList is the comma list of extensions to read only (blank = all)
     * @param	boolean		If set, then the path is prepended the filenames. Otherwise only the filenames are returned in the array
     * @param	string		$order is sorting: 1= sort alphabetically, 'mtime' = sort by modification time.
     * @param	string		A comma seperated list of filenames to exclude, no wildcards
     * @return	array		Array of the files found
     */
    public function getFilesInDir($path, $extensionList = '', $prependPath = 0, $order = '', $excludePattern = '')
    {

        // Initialize variabels:
        $filearray = array();
        $sortarray = array();
        $path = rtrim($path, '/\\');

        // Find files+directories:
        if (@is_dir($path)) {
            $extensionList = strtolower($extensionList);
            $d = dir($path);
            if (is_object($d)) {
                while ($entry = $d->read()) {
                    if (@is_file($path . '/' . $entry)) {
                        $fI = pathinfo($entry);

                        if (isset($fI['extension'])) {
                            $key = md5($path . '/' . $entry); // Don't change this ever - extensions may depend on the fact that the hash is an md5 of the path! (import/export extension)
                            if ((!strlen($extensionList) ||
                                \App\Utility\GeneralUtility::inList($extensionList, strtolower($fI['extension']))) &&
                                (!strlen($excludePattern) || !preg_match('/^' . $excludePattern . '$/', $entry))) {
                                $filearray[$key] = ($prependPath ? $path . '/' : '') . $entry;
                                if ($order == 'mtime') {
                                    $sortarray[$key] = filemtime($path . '/' . $entry);
                                } elseif ($order) {
                                    $sortarray[$key] = $entry;
                                }
                            }
                        }
                    }
                }
                $d->close();
            } else
                return 'error opening path: "' . $path . '"';
        }

        // Sort them:
        if ($order) {
            asort($sortarray);
            $newArr = array();
            foreach ($sortarray as $k => $v) {
                $newArr[$k] = $filearray[$k];
            }
            $filearray = $newArr;
        }

        // Return result
        reset($filearray);
        return $filearray;
    }
    
    /**
     * Wrapper function for rmdir, allowing recursive deletion of folders and files
     *
     * @param string $path Absolute path to folder, see PHP rmdir() function. Removes trailing slash internally.
     * @param boolean $removeNonEmpty Allow deletion of non-empty directories
     * @return boolean TRUE if @rmdir went well!
     */
    public function rmdir($path, $removeNonEmpty = FALSE)
    {
        $OK = FALSE;
        $path = rtrim($path, '/\\');

        if (file_exists($path)) {
            $OK = TRUE;

            if (is_dir($path)) {
                if ($removeNonEmpty == TRUE && $handle = opendir($path)) {
                    while ($OK && FALSE !== ($file = readdir($handle))) {
                        if ($file == '.' || $file == '..') {
                            continue;
                        }
                        $OK = $this->rmdir($path . '/' . $file, $removeNonEmpty);
                    }
                    closedir($handle);
                }
                if ($OK) {
                    $OK = rmdir($path);
                }
            } else { // If $dirname is a file, simply remove it
                $OK = unlink($path);
            }

            clearstatcache();
        }

        return $OK;
    }
    
    public function recurseCopy($src, $dst, $overwrite_files = false)
    {
        $dir = opendir($src);
        $dst = rtrim($dst, '/\\');
        if (!is_dir($dst)) {
            $this->mkdir($dst);
        }
        $result = true;
        while (false !== ( $file = readdir($dir))) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    $result = $result && $this->recurseCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    if (!file_exists($dst . '/' . $file) || $overwrite_files == true) {
                        $result = $result && copy($src . '/' . $file, $dst . '/' . $file);
                        $this->fixPermissions($dst . '/' . $file);
                    }
                }
            }
        }
        closedir($dir);
        return $result;
    }
}