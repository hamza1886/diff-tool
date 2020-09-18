(function () {
    'use strict';

    let xhr, xhr2;
    let referenceId, iframeReferenceId;

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        onOpen: function (toast) {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    window.addEventListener('load', function () {
        const maxFilesize = 100 * 1024;
        const allowedMimetypes = ['text/plain'];

        const forms = document.getElementsByClassName('needs-validation');

        Array.prototype.filter.call(forms, function (form) {
            const oldFileEl = form.querySelector('#old-file');
            const newFileEl = form.querySelector('#new-file');

            oldFileEl.addEventListener('change', function () {
                if (this.files.length === 0) {
                    form.querySelector('[for=old-file]').innerText = 'Choose old file...';
                } else {
                    form.querySelector('[for=old-file]').innerText = this.files[0].name;
                }
            }, false);

            newFileEl.addEventListener('change', function () {
                if (this.files.length === 0) {
                    form.querySelector('[for=new-file]').innerText = 'Choose new file...';
                } else {
                    form.querySelector('[for=new-file]').innerText = this.files[0].name;
                }
            }, false);

            form.addEventListener('submit', function (event) {
                event.stopPropagation();
                event.preventDefault();
                form.classList.add('was-validated');
                form.querySelector('.loading').style.display = '';
                document.querySelector('iframe').src = '';
                document.querySelector('iframe').style.height = '10rem';

                const oldFile = oldFileEl.files[0];
                const newFile = newFileEl.files[0];

                if (form.checkValidity() === false) {
                    document.querySelector('.loading').style.display = 'none';
                    return false;
                }

                if (!allowedMimetypes.includes(oldFile.type) || oldFile.size > maxFilesize) {
                    document.querySelector('.loading').style.display = 'none';
                    Toast.fire({
                        icon: 'error',
                        title: 'Text files (.txt) of max. ' + (maxFilesize / 1024) + ' kB are allowed',
                    });
                    return false;
                }

                if (!allowedMimetypes.includes(newFile.type) || newFile.size > maxFilesize) {
                    document.querySelector('.loading').style.display = 'none';
                    Toast.fire({
                        icon: 'error',
                        title: 'Text files (.txt) of max. ' + (maxFilesize / 1024) + ' kB are allowed',
                    });
                    return false;
                }

                // prepare files for upload
                const formData = new FormData();
                formData.append('old_file', oldFile);
                formData.append('new_file', newFile);

                makeRequest(formData);
            }, false);

            form.addEventListener('reset', function (event) {
                form.classList.remove('was-validated');
                form.querySelector('.loading').style.display = 'none';
                form.querySelector('[for=old-file]').innerText = 'Choose old file...';
                form.querySelector('[for=new-file]').innerText = 'Choose new file...';
            }, false);
        });
    }, false);

    function makeRequest(formData) {
        if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
            xhr = xhr2 = new XMLHttpRequest();
        } else if (window.ActiveXObject) { // IE 6 and older
            xhr = xhr2 = new ActiveXObject("Microsoft.XMLHTTP");
        }

        if (!xhr || !xhr2) {
            alert('Giving up :( Cannot create an XMLHTTP instance');
            return false;
        }

        xhr.open('POST', 'file_upload.php', true);
        xhr.onreadystatechange = handleResponse;
        xhr.send(formData);
    }

    function handleResponse() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            document.querySelector('.loading').style.display = 'none';

            const responseJSON = JSON.parse(xhr.responseText);
            if (xhr.status === 200) {
                document.querySelector('#output-container').style.display = 'block';
                document.querySelector('#output-container .loading').classList.add('d-flex');
                document.querySelector('#output-container .loading').classList.remove('d-none');
                referenceId = setInterval(checkDiffFile, 1000, responseJSON.data.out_filename);
            } else {
                document.querySelector('#output-container').style.display = 'none';
                Toast.fire({
                    icon: 'error',
                    title: responseJSON.error.message,
                });
            }
        }
    }

    function checkDiffFile(out_filename) {
        xhr2.open('GET', 'check_diff_file.php?out_filename=' + encodeURIComponent(out_filename), true);
        xhr2.onreadystatechange = function () {
            if (xhr2.readyState === XMLHttpRequest.DONE && xhr2.status === 200) {
                const responseJSON = JSON.parse(xhr2.responseText);
                if (responseJSON.data.finish) {
                    clearInterval(referenceId);

                    document.querySelector('#output-container iframe').src = out_filename;
                    document.querySelector('#output-container .loading').classList.add('d-none');
                    document.querySelector('#output-container .loading').classList.remove('d-flex');
                    document.forms[0].reset();

                    const iframe = document.querySelector('iframe');
                    iframeReferenceId = setInterval(checkIframeLoaded, 1000, iframe, responseJSON.message);
                }
            }
        };
        xhr2.send();
    }

    function checkIframeLoaded(iframe, message) {
        const iframeDocument = iframe.contentWindow.document;
        if (iframe.clientHeight > 0 && iframeDocument.querySelector('body').children.length > 0) {
            if (iframeDocument.documentElement.offsetHeight === iframeDocument.documentElement.scrollHeight) {
                iframe.style.height = (parseInt(iframe.style.height) + 10) + 'rem';
            } else {
                clearInterval(iframeReferenceId);

                Toast.fire({
                    icon: 'success',
                    title: message,
                });
            }
        }
    }
})();