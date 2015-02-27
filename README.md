# VirusTotal Image Generator
Generates a dynamic image with a VirusTotal scan result.
Should be pretty straigthforward and give a basic idea of how you can generate your own images with PHP

### Configuration
All configuration is located in the image.php file.
- VirusTotal API key
- Font
- Logo
- Cache time (default: 6hrs)
- Size

### Usage
Can be called with the following request, where the hash is that of the file:
- image.php?q=[SHA256]
- image.php?q=[SHA1]
- image.php?q=[MD5]

### Example image
![alt tag](http://i.imgur.com/jqAcK6S.png)

Of this file:
https://www.virustotal.com/en/file/aa2298a7cb4f22b6301f31a95430ee50d6d5a26d75cb302d78361009605ad327/analysis/1425035432/
