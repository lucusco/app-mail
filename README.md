## Email Sender App (back-end project)

Project developed to implement PHPMailer and Monolog libraries as part of improve PHP acknowledgement.  

Also, some Symfony packages were implemented to deal with Routes, Requests and Responses. The aim is to be like a 'real world' application,  besides, of course, to understand how this packages works.  
Throughout development became the necessity to store data, so Postgres was the chosen one. 
RabbitMQ was chosen to manage the send queue. When a request is received, it calls the function to send the email. 

A simple interface was created to interact with user and give him the possibility to send an email. Bellow, the interface screenshoot:

![alt text](https://github.com/lucusco/app-mail/blob/main/docs/screenshot1.png?raw=true)

#### Main Goal
Be able to send an email

#### Technologies used
- PHP@7.4
- Apache Webserver
- RabbitMQ
- Docker
- PostgreSQL

If you wish to contributte or suggest improvments, fell free to contact me.

**Developed by**  
Luis Claudio Bueno  
_lu.cusco@gmail.com_  
_github.com/lucusco_  
_linkedin.com/in/luisclaudiombueno_
