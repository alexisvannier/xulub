Usage: phimx [options] -d <directory1> [-d <directory2> ...] [xmiFile]

PHiMX is a command-line tool to generate XMI code of a project in PHP5.
  
-d <directory>
   Add a directory of PHP scripts to parse.
   
[xmiFile]
   Path of the file containing the XMI code ("./default.xmi" by default).
   You can use relative or absolute path.   
      
Options:
  -h --help
     Print this message.
     
  -r --recursive
     Parse recursively PHP scripts directories.
     
  -m --mask <mask>
     File mask wich may contain common valid file name symbols (*: any number
     of characters, ?: any single character, [c,x-z]: any character enclosed
     by the brackets). You may enter several file masks separated with commas.
     You may use exclude masks with the character '|', an exclude mask is one
     or multiple file masks that must not be matched by the files matching
     the mask (eg: *.php,*.inc|toto.php,test*.php). Set to "*.php" by default.
     
  -i --include-path <directory>
     Add a directory path to the include_path, you can use relative or absolute
     path. You can add several include_path directories.
     
  -f --formatter
     Fully qualified name of the formatter Class to use,
     "phimx.formatter.PHiMX_XMI_1_1" by default (defined in the
     "phimx/formatter/PHiMX_XMI_1_1.php" file in the pear classes directory).
     
  -t --trace
     Create a "./phimx.log" file for logging parse. You must
     have installed PEAR::Log package to use this option.

Examples:
  phimx -d src/dir1 -d /home/toto/project/src/dir2
  
     Parse several directories of php scripts ("*.php" by default). The XMI
     code is saved in the "./default.xmi" file.
     

  phimx -d src -r -m "*.php,*.php5" project.xmi
  
     Parse recursively the "src" directory php files with php and php5
     extension. The XMI code is saved in the "project.xmi" file.
     
      
  phimx -d src -i lib -i /usr/pear doc/project.xmi

     Parse the "src" directory php files using 2 directories to add to the
     include_path. The XMI code is saved in the "project.xmi" file in the
     relative "doc" directory.    
