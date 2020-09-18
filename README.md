# diff-tool

A tool to show Git like file diffs on command line or in HTML format.

## Getting Started

### Install dependencies

- [Apache](https://httpd.apache.org/) or [Ngnix](https://www.nginx.com/) is required to run the web server.
- [PHP](https://www.php.net/) is required to run backend code. PHP extension `fileinfo` needs to be enabled in `php.ini`.
- [Python3](https://www.python.org/) is required to run *diff-tool* core functionality, based on `difflib` python package which comes with Python.

### Adjust permissions

Adjust directory permissions to allow file upload to work

```shell script
sudo chmod -R 777 upload/
``` 

### Run *diff-tool* web app

Visit `http://127.0.0.1/diff-tool/index.html` in browser.

**Output**

![web_output](extras/web_output.png)

## License

The *diff-tool* is open-source software licensed under the MIT [license](LICENSE).
