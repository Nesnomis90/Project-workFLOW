# Project-workFLOW
A database driven website for booking meeting rooms and managing users
Built with MySQL, PHP, HTML and CSS

Made by following a tutorial on sitepoint.com

System construction graphic:
http://i3.sitepoint.com/graphics/1733_first_principles.thumb.jpg

Web interaction system flow:
-The visitor’s web browser requests the web page using a standard URL.
-The web server software (typically Apache) recognizes that the requested file is 
a PHP script, so the server fires up the PHP interpreter to execute the code contained in the file.
-Certain PHP commands (which will be the focus of this chapter) connect to the 
MySQL database and request the content that belongs in the web page.
-The MySQL database responds by sending the requested content to the PHP script.
-The PHP script stores the content into one or more PHP variables, then uses 
echo statements to output the content as part of the web page.
-The PHP interpreter finishes up by handing a copy of the HTML it has created to the web server.
-The web server sends the HTML to the web browser as it would a plain HTML file, 
except that instead of coming directly from an HTML file, the page is the output 
provided by the PHP interpreter.

-------------------------------------------------------------------------------
--		MySQL
-------------------------------------------------------------------------------
How to communicate with MySQL through command prompt:
type: mysql -u root -p
type: TheRootPassword
type: SHOW DATABASES;
type: CREATE DATABASE databasename;
type: USE databasename;

example CREATE TABLE joke (	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
							joketext TEXT,
							jokedate DATE NOT NULL)
							DEFAULT CHARACTER SET utf8;
							
type: CREATE TABLE joke ( -> id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, -> joketext TEXT, ->
jokedate DATE NOT NULL -> ) DEFAULT CHARACTER SET utf8;

type: SHOW TABLES;
type: DESCRIBE databasename;

example INSERT INTO joke
		SET joketext = "Why did the chicken cross the road? To get to the other side!", jokedate = "2009-04-01";
		
type: INSERT INTO joke SET ->
		joketext = "Why did the chicken cross the road? To get to the other side!", ->
		jokedate = "2009-04-01";

type: SELECT * FROM databasename; (Gets everything)
type: SELECT id, jokedate FROM joke; (Gets only id and jokedate)
type: SELECT id, LEFT(joketext, 20), jokedate FROM joke; (Gets id, joketext and jokedate. Joketext is limited to 20 chars)

type: SELECT COUNT(*) FROM databasename; (counts the number of rows in the table)
type: SELECT joketext FROM joke WHERE joketext LIKE "%chicken%"; (gets joketext that includes the word chicken"

etc....

-------------------------------------------------------------------------------
--		PHP
-------------------------------------------------------------------------------
Variables, declaration and string combination
$variablename = assignedvalue;
$avariable = 'text';
$anothervariable = "$avariable andmoretext";
$anothervariable = $avariable . "andmoretext";
$avariable = digit;

Arrays
$myarray = array('one', 2, '3');
echo $myarray[0];					//Outputs 'one'
$myarray[1] = 'two';				//Sets index 1 to 'two'

$birthdays = array('Kevin' => '1978-04-12', 'Stephanie' => '1980-05-16', 'David' => '1983-09-09');
$birthdays['Kevin'] = '1979-04-12';
echo 'My birthday is: ' . $birthdays['Kevin'];

Retrieving info from website
$_GET - URL diplays query and can submit info directly
$_POST - Query is hidden, only accepts info through input
$_REQUEST - Accepts both