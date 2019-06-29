<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use WhiteBox\Filesystem\RecursiveDirectoryBrowser;
use Exception;
use WhiteBox\Helpers\MagicalArray;
use WhiteBox\Routing\Abstractions\A_RouteManager;
use WhiteBox\Routing\Abstractions\T_RouteLoader;
use WhiteBox\Routing\Abstractions\T_RouteManager;
use WhiteBox\Routing\Router;



/**
 * Class RouteLoader
 * @package WhiteBox\Routing
 */
class RouteLoader{
    /////////////////////////////////////////////////////////////////////////
    //Traits used
    /////////////////////////////////////////////////////////////////////////
    use T_RouteLoader;

    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
	/**Loads all the routes located in the path (use this only in case of extreme emergency/bad code)
	 * Use this inside your code
	 * @param T_RouteManager $manager being the T_RouteManager to add the routes to
	 * @param string $alias being the name of the Router as used in the route declaration files
	 * @return void
	 * @throws Exception
	 */
    public function loadRoutes(/*A_RouteManager*/ $manager, string $alias): void{
        $this->getPhpFiles()
        ->forEach(static function(string $phpFile) use($manager, $alias){
            ${$alias} = $manager;
            require_once "{$phpFile}";
        });
    }

    /**Generate a loader file each time it is called (to the given file URI)
     * Can be used in your code or to do a generator script (in shell for instance)
     * @param string $autoloaderFileURI
     * @return string
     * @throws Exception if it cannot access to the autoloader file
     */
    public function generateLoaderFile(string $autoloaderFileURI): string{
        $phpFiles = $this->getPhpFiles();

        $autoloader = fopen($autoloaderFileURI,"w+");
        if(!$autoloader)
            throw new Exception("failed to create the autoloader file for the route loader.");

        fwrite($autoloader, "<?php\n");
        $phpFiles->forEach(static function(string $filePath) use($autoloader){
            fwrite($autoloader, "require_once '{$filePath}';\n");
        });
        fclose($autoloader);

        return (string)realpath($autoloaderFileURI);
    }

	/**Retrieves the PHP files path from the directory of this loader
	 * @return MagicalArray
	 * @throws Exception
	 */
    protected function getPhpFiles(): MagicalArray{
        return (new RecursiveDirectoryBrowser($this->path))
        ->toMagicalArray()
        ->filter(static function(string $elem){
            ["extension" => $extension] = pathinfo($elem);
			return $extension === "php";
        })
        ->sortBy(static function(string $lhs, string $rhs): int{
            if($lhs === $rhs)
                return 0;
            else{
                $depthOf = static function(string $str): int{
                    $str = str_replace("\\", "/", $str);
                    $d = substr_count($str, "/");
                    if($d === false)
                        return 1;
                    return $d;
                };

                $depthLhs = $depthOf($lhs);
                $depthRhs = $depthOf($rhs);

                if($depthLhs === $depthRhs)
                    return $lhs <=> $rhs;
                else
                    return $depthLhs <=> $depthRhs;
            }
        });
    }
}