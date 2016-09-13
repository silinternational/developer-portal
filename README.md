# Developer Portal #

## Environment / Project Setup ##
1. Install [VirtualBox](http://www.virtualbox.org/wiki/Downloads)
2. Install [Vagrant](http://downloads.vagrantup.com/)
3. Clone the repository:  
   ```git clone git@github.com:silinternational/developer-portal.git```
4. Copy the ```local.env.dist``` file to ```local.env```, and update its 
   contents appropriately.
5. Open a shell/terminal in the project's root folder.
6. Launch environment with ```vagrant up```
7. Modify your hosts file per the instructions below.
8. Open browser and go to <http://your-domain-name/>
9. If you need to make yourself an admin, login first (to make sure your user
   record exists), then go to    <http://your-domain-name/phpmyadmin>.
   Login with the project's database credentials, find your user in the user
   table, and change the role to 'admin'.

### local.env reference ###
- ```SHOW_POPULAR_APIS``` (boolean)  
  Whether to show the most popular APIs (based on the number of approved keys).
  If false, the file ```application/protected/views/partials/home-lower-right.php```
  will be shown (which you can overwrite with whatever content you want during
  your deployment process).

## Reference Links ##
1. Yii Bootstrap extension - http://www.yiiframework.com/extension/bootstrap

## How To ##
1. Reset Database:  
   ```./yiic dbreset db``` for main database or  
   ```./yiic dbreset testDb``` for test database.

## Hosts file modification for running Developer Portal locally ##
Add the following line to your hosts file (replacing ```your-domain-name``` with
the domain name you want to use):
```192.168.33.10 your-domain-name```

## Troubleshooting ##
If you try to access <http://your-domain-name/phpmyadmin/> and get the following 
error... 

    phpMyAdmin - Error  
    Wrong permissions on configuration file, should not be world writable!

... then add the following line to the end of 
`vendor\phpmyadmin\phpmyadmin\config.inc.php`: 

    $cfg['CheckConfigurationPermissions'] = false;
