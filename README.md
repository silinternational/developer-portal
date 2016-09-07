# Developer Portal #

## Environment / Project Setup ##
1. Install [VirtualBox](http://www.virtualbox.org/wiki/Downloads)
2. Install [Vagrant](http://downloads.vagrantup.com/)
3. Clone the repository:  
   ```git clone git@github.com:silinternational/developer-portal.git```
4. Copy the ```local.json.dist``` file to ```local.json```, and update its 
   contents appropriately. Some entries (such as adding a path alias for 
   phpmyadmin to the Apache vhost file) are specific to a development 
   environment and should not be used in production. 
5. Using shell/terminal, get to the vagrant folder in the project home folder
6. Launch environment with ```vagrant up```
7. Modify your hosts file per the instructions below.
8. Open browser and go to <http://developer-portal.local/>
9. If you need to make yourself an admin, login first, then go to
   <http://developer-portal.local/phpmyadmin>. Login with project DB credentials. Find
   your user in the user table and change role to 'admin'. Logout and login
   again to become an admin.

### local.env reference ###
- ```SHOW_POPULAR_APIS``` (boolean)  
  Whether to show the most popular APIs (based on the number of approved keys).
  If false, the file ```application/protected/views/partials/home-lower-right.php```
  will be shown (which you can overwrite with whatever content you want during
  your deployment process).

## Reference Links ##
1. Yii Bootstrap extension - http://www.yiiframework.com/extension/bootstrap
2. Swagger for API documentation - https://developers.helloreverb.com/swagger/

## How To ##
1. Reset Database:  
   ```./yiic dbreset db``` for main database or  
   ```./yiic dbreset testDb``` for test database.

## Hosts file modification for Developer Portal ##
Add the following line to your hosts file to be able to connect to apiaxle from
your desktop:
```192.168.33.10 developer-portal.local```

## Troubleshooting ##
If you try to access <http://developer-portal.local/phpmyadmin/> and get the following 
error... 

    phpMyAdmin - Error  
    Wrong permissions on configuration file, should not be world writable!

... then add the following line to the end of 
`vendor\phpmyadmin\phpmyadmin\config.inc.php`: 

    $cfg['CheckConfigurationPermissions'] = false;


## Questions ##
1. How should we handle situations where a user is granted a Key to an API, but
   later is removed from the group that allowed them to see that API?
