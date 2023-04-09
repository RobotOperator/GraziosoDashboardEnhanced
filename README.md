# GraziosoDashboardEnhanced
My enhancements of the Grazioso Dashboard for the first artifact of CS499.

## About the Grazioso Salvare Dashboard
This dashboard operates as a user interface to interact with data stored in a MongoDB database using a PHP browser interface that also utilizes PHP endpoints as a middleware for interacting with the database. The dashboard facilitates a web interface to view the records retrieved based on filtering and interactions with the widgets and can be adapted to present data for a variety of different datasets.

## Motivation
The primary driving factor behind this project is to provide a contracted client’s desired product of a full stack solution aimed at easily organizing, filtering, and sorting data through a web browser. The dashboard is currently designed for use with a collection of animal records for the Grazioso client but could be easily adapted to serve a variety of records using the PHP server code and the MongoDB database.

## Getting Started
To get a local instance of the project up and running follow the steps below.

  1.	Start an instance of MongoDB – ‘mongod_ctl start-noauth’
  2.	Import a dataset into the local database to interact with – ‘mongoimport --db AAC --collection animals --type=csv --headerline --file= /usr/local/datasets/aac_shelter_outcomes.csv --port 5247’ #Replace the port with your local instance port and the specified db and collection as desired for your data
  3.	Start the mongo command line interface – ‘mongo’ or ‘mongosh’
  4.	Create a user to connect to the database (AAC in this example) and interact with the data - 
    <br>a.	‘use admin’
    <br>b.	‘db.createUser({user: “aacuser”, pwd: passwordPrompt(), roles: [{role: “readWrite”, db “AAC”}]})’
    <br>c.	Enter the password for the new user at the prompt.
  5.	Copy an authentication configuration for the mongod service to the current configuration – ‘sudo cp /etc/mongod_withauth.conf /etc/mongod.conf’
  6.	Restart the mongod service to run with authentication required – ‘sudo systemctl restart mongod.service’
  7.	Host the PHP and HTML code on a web server (Apache, Nginx, IIS, etc.) with PHP installed
    <br>a.	Ensure you have the PECL PHP driver installed. Instructions available at: https://www.php.net/manual/en/mongodb.installation.pecl.php
    <br>b.	Extract vendor.zip in the same hosted directory as the rest of the code
    <br>c.	Update the port used to create MongoDB connections in config.php on line 15 if different from 27017
    <br>d.	Optional: update any references to localhost within config.php, pro.php, and dashboard.php if you are hosting the database on a different server.
  8.	Use a web browser to navigate to http://<server>/login.html
  9.	Enter the user credentials that you created for accessing the MongoDB database and you will be redirected to the project Dashboard available at dashboard.php



