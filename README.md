`````````````````````````````````````````````````````````````````````````````````````
This is a battleship game based on coordinates from 1 - 10  and A - H where you have 
to enter for example A5 to send a shot on the board where the numbers of ships are 
3(1 destroyer which have 5 health points and 2 battleships which have 4 health points bar)
the idea is to sink them and in the end u can see how many shots you used
			
/************** SET UP THE GAME WITH APACHE ************************/
	<VirtualHost *:80>
		DocumentRoot "<Enter your root folder path >"
	        ServerName <enter your prefered address url name>
	        <Directory "<Enter your root folder path >"
			DirectoryIndex index.php
	                AllowOverride All
	                Order allow,deny
	                Allow from all
	         </Directory>
	</VirtualHost>

	Afterwards you need to go to your hosts file if u are using UNIX based OS its in:
	1) vi /etc/hosts
	2) 127.0.0.1 <your prefered address url name>
/************** SET UP THE GAME WITH APACHE ************************/
`````````````````````````````````````````````````````````````````````````````````````
