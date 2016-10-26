# VirusTotal Image Generator
Generates a dynamic image with a VirusTotal scan result.
Should be pretty straigthforward and give a basic idea of how you can generate your own images with PHP

### Configuration
All configuration is located in the image.php file.
- VirusTotal API key !REQUIRED
- Font
- Logo
- Cache time (default: 3hrs)
- Cache directory
- Size

Requires the gd2 PHP library;
http://php.net/manual/en/image.installation.php

A cache is saved both clientside as serverside.
The serverside uses a directory while for the clientside the headers are set.
Both use the same cache time value.

### Usage
Can be called with the following request, where the hash is that of the file:
- image.php?q=[SHA256]
- image.php?q=[SHA1]
- image.php?q=[MD5]

### Example image
![Virustotal Result Image](http://i.imgur.com/vLpLjSq.png)

Of this file:
https://www.virustotal.com/en/file/aa2298a7cb4f22b6301f31a95430ee50d6d5a26d75cb302d78361009605ad327/analysis/1425035432/
