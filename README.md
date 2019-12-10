1. Login to Laravel Forge and spin up a server. 
2. Run the packages recipie
3. Follow this tutorial to install redis https://www.digitalocean.com/community/tutorials/how-to-install-redis-from-source-on-ubuntu-18-04
4. Make sure you are runing php 7.2. 7.3 has some issues "Notice: unserialize(): Error at offset 3273 of 4376 bytes at /home/forge/default/vendor/symfony/security-core/Authentication/Token/AbstractToken.php:154)"
5. install php-amqp. You should be able to do an apt-cache search amqp
6. install rabbit mq https://tecadmin.net/install-rabbitmq-server-on-ubuntu/
7. install messenger component https://symfony.com/doc/current/messenger.html


    Make sure you setup nscs/nscs-bundle as a private repo 
    https://getcomposer.org/doc/05-repositories.md#using-private-repositories
    before you deploy
    
    Disable amqp rabbit mq on the app as the prod server does not have rabbit mq instlled and is
    causing errors. 
    
    when deploying look at the PHPinfo for your local. You 
    will need to copy that on staging/production. for example
    you will need to install GD and amqp
    
    
    SETTING UP THE QUEUE(TRANSPORT) SYSTEM
    
    ./bin/console messenger:consume 
    
    _______________________________________________
    You need to disable only full group by on MYSQL
    
    https://stackoverflow.com/questions/23921117/disable-only-full-group-by/23921234
    
    If you want to disable permanently error "Expression #N of SELECT list is not in GROUP BY clause and contains nonaggregated column 'db.table.COL' which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by" do those steps:
    
    sudo nano /etc/mysql/my.cnf
    Add this to the end of the file
    
    [mysqld]  
    sql_mode = "STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"
    sudo service mysql restart to restart MySQL
    
    This will disable ONLY_FULL_GROUP_BY for ALL users
    _______________________________________________