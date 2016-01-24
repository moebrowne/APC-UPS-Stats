'use strict';

var io = require('socket.io')(3000);
var fs = require('fs');

io.on('connection', function(socket){
	console.log('WS: user connected');
});

fs.watchFile('../apcupsd.status.json', function(curr, prev) {
	var JSONData = fs.readFileSync('../apcupsd.status.json', { flags: '+w' }).toString();
	JSONData = (JSONData !== '') ? JSON.parse(JSONData):[];
	io.emit('data', JSONData);
	console.log('file changed');
});