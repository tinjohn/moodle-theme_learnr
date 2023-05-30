

window.addEventListener('load', function () {
    // get the progressbar
    var pr = document.getElementsByClassName('progress')[0];
    const prbar = document.getElementsByClassName('progress-bar progress-bar-info')[0];
    console.log('pr' + pr);
    console.log('prbar' + prbar);
});

// https://riptutorial.com/javascript/example/20197/listening-to-ajax-events-at-a-global-level
// Store a reference to the native method
let send = XMLHttpRequest.prototype.send;
const prbar = document.getElementsByClassName('progress-bar progress-bar-info')[0];
console.log(document.getElementsByClassName('progress-bar progress-bar-infos')[0]);

    // Overwrite the native method
    XMLHttpRequest.prototype.send = function() {
        // tinjohn get Request Payload from send arguments
        payload = arguments[0];
        console.log("arguments 0 send-----" + payload);
        var isValidJSON = true;
        try { JSON.parse(payload) } catch { isValidJSON = false }
        if(isValidJSON) {
            // Assign an event listener
            // and remove it again
            // https://www.mediaevent.de/javascript/remove-event-listener.html
            this.addEventListener("load", function removeMe () { 
                readAJAXrequestsend(payload);
                this.removeEventListener("load", removeMe);
            }, false);
        }
        // Call the stored reference to the native method
        send.apply(this, arguments);
    };

    function readAJAXrequestsend (payload) {
        console.log("readAJAXrequestsend-----payload---------" + payload);

        var isValidJSON = true;
        try { JSON.parse(payload) } catch { isValidJSON = false }
        if(isValidJSON) {
            const plo = JSON.parse(payload);
            console.log(plo[0]);

            /*    
            console.log(plo[0].methodname);
            console.log(plo[0].args.cmid);
            console.log(plo[0].args.completed);
            */
            // update_activity_completion_status_manually in completion/external.php
            if (plo[0].methodname.match("(.*)core_completion_update_activity_completion_status_manually(.*)")) 
            {
                if (plo[0].args.completed) {
                    addProgress();
                } else {
                    var newwidth = parseInt(prbar.style.width) - parseInt(prbar.getAttribute('progress-steps'));
                    if((parseInt(prbar.style.width) - parseInt(prbar.getAttribute('progress-steps'))) < 0) {
                        var newwidth = 0
                    }
                    prbar.style.width = newwidth + '%';      
                }
            }
            if (plo[0].methodname.match("(.*)core_xapi_statement_post(.*)"))
            {
                isValidJSON = true;
                try { JSON.parse(plo[0].args.requestjson) } catch { isValidJSON = false }
                if(isValidJSON) {
                    const xapiReq = JSON.parse(plo[0].args.requestjson);
                    console.log(xapiReq);
                    if (xapiReq[0].result.completion && xapiReq[0].result.success) {
                        // check if initially successed already - in completemods thus send the context id
                        var contextid = xapiReq[0].object.id;
                        var sender = xapiReq[0].actor.account.homepage;
                        // it is an iframe - hook is in the iframe call addProgress to window.parent
                        window.parent.postMessage({method : "addProgress", contextid : contextid}, sender);
                    }
                }        
            }
            payload = '';
        }
    }

    function addProgress (contextid) {
        const iframeprbar = document.getElementsByClassName('progress-bar progress-bar-info')[0];
        if(iframeprbar === null) {
            return
        } 
        if(contextid != null) {
            const completedmods = iframeprbar.getAttribute('completedmods');
            if(completedmods.match('(.*)' + contextid + '(.*)')) {
                console.log(contextid + " war schon fertig");
                return;
            } 
        }
        iframeprbar.style.width = (parseInt(iframeprbar.style.width) + parseInt(iframeprbar.getAttribute('progress-steps'))) + '%';              
    }

    window.addEventListener('message', function(e) {
        console.log('---got mail from iframe---', e.data);
        if(e.data.method == 'addProgress') {
            const [trash, contextid] = e.data.contextid.split(/\/(?=[^\/]+$)/);
            addProgress(contextid);
        }
    });


