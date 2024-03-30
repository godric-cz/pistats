(() => {
    const src = document.currentScript.src
    const target = src.slice(0, src.lastIndexOf('/')) + '/record.php'
    const rn = () => Math.floor(1e8 + Math.random() * (1e9 - 1e8)) + ''

    if (['localhost', '127.0.0.1'].includes(document.location.hostname)) {
        return
    }

    let uid = window.localStorage.getItem('stat_uid')
    if (uid === null) {
        uid = rn() + rn()
        window.localStorage.setItem('stat_uid', uid)
    }

    const r = new XMLHttpRequest()
    r.open('POST', target, true)
    r.send(JSON.stringify({
        'referrer': document.referrer,
        'uid': uid,
        'path': location.pathname+(location.search?location.search:"")
    }))
})()
