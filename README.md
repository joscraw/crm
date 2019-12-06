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