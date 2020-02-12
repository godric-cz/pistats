let src = document.currentScript.src
let target = src.slice(0, src.lastIndexOf('/')) + '/record.php'

let r = new XMLHttpRequest()
r.open('POST', target, true)
/*
r.onreadystatechange = function () {
    if (r.readyState != 4) return
    console.log(r.status)
    console.log(r.responseText)
};
*/
r.send(JSON.stringify({
    'referrer': document.referrer
}))
