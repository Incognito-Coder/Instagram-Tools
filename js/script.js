function Run() {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            var JData = JSON.parse(this.responseText);
            document.getElementById("files").hidden = false;
            if (JData.caption !== null) {
                document.getElementById("caption").innerHTML = JData.caption.replace('\n', '<br>');
                document.getElementById("caption").hidden = false;
                if (isUnicode(document.getElementById('caption').textContent)) {
                    document.getElementById('caption').style.direction = 'rtl';
                }
                else {
                    document.getElementById('caption').style.direction = 'ltr';
                }
            }
            var data = new FormData();
            data.append("file", JData.file);
            var xhr = new XMLHttpRequest();
            xhr.addEventListener("readystatechange", function () {
                if (this.readyState === 4) {
                    var json = JSON.parse(this.responseText);
                    if (JData.type == 'image') {
                        document.getElementById("thumb").hidden = false;
                        document.getElementById("thumb").src = json.path;
                    }
                }
            });
            xhr.open("POST", "proxy.php");
            xhr.send(data);
            if (JData.type == 'side') {
                var count = 0;
                document.getElementById("thumb").hidden = true;
                document.getElementById('carouselExampleControls').hidden = false;
                JData.data.forEach((element, index) => {
                    let slide = document.createElement("div");
                    if (index == 0) {
                        slide.className = "carousel-item active";
                    } else {
                        slide.className = "carousel-item";
                    }
                    var data = new FormData();
                    data.append("file", element.file);
                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", "proxy.php");
                    xhr.send(data);
                    xhr.addEventListener("readystatechange", function () {
                        if (this.readyState === 4) {
                            var json = JSON.parse(this.responseText);
                            if (element.type == "image") {
                                slide.innerHTML = "<img class=\"d-block w-100\" src=" + json.path + ">";
                            } else if (element.type == "video") {
                                slide.innerHTML = "<video class=\"d-block w-100\" controls\"><source src=" + json.path + " type=\"video/mp4\"/></video>";
                            }
                        }
                    });
                    document.getElementById('carousel-child').appendChild(slide);
                    let btn = document.createElement("a");
                    btn.className = "btn p-1 m-1";
                    btn.style.backgroundColor = "#00b0ff";
                    btn.style.color = 'white';
                    btn.innerHTML = "<i class=\"fas fa-download\"></i> " + count++;
                    btn.href = element.file;
                    btn.target = '_blank';
                    document.getElementById('files').appendChild(btn);
                });
            } else {
                let btn = document.createElement("a");
                btn.className = "btn p-1 m-1";
                btn.style.backgroundColor = "#00b0ff";
                btn.style.color = 'white';
                btn.innerHTML = "<i class=\"fas fa-download\"></i> Save";
                btn.href = JData.file;
                btn.target = '_blank';
                document.getElementById('files').appendChild(btn);
            }
        }
    };
    xmlhttp.open("GET", "instagram.php?link=" + document.getElementById("url-field").value, true);
    xmlhttp.send();
}
function isUnicode(str) {
    var letters = [];
    for (var i = 0; i <= str.length; i++) {
        letters[i] = str.substring((i - 1), i);
        if (letters[i].charCodeAt() > 255) { return true; }
    }
    return false;
}