# OTRS_DB_API
A json/xml API for fetching and updating tickets in OTRS without using OTRS.

Usage:<br>
Place these files on your OTRS instance somewhere in /var/www/html. This would allow you to access the API via http://yourotrsinstancedomain.tld/api/ and the Test page via http://yourotrsinstancedomain.tld/test/ without disrupting your OTRS experience.<br><br>
NOTE: This is meant to be an api accessed via server side scripting on another server. The test directory is merely a POC and offers a security risk if using customer facing as you can manipulate the javascript.

Calls:<hr>

Url: <strong>getQueues.json</strong><br>
Method: GET<br>
Description: Returns all available OTRS Queues and information about them.
<br><br>

Url: <strong>getArticles.json</strong><br>
Method: POST<br>
Description: Returns all articles related to a ticket with newest first.
Parameters:<br><table>
<thead><tr><th>param</th><th>type</th><th>description</th><th>required</th></tr></thead>
<tr>
	<td>ticket_id</td>
	<td>int</td>
	<td>The ticket id used to locate the articles attached</td>
	<td>yes</td>
</td>
</table>
