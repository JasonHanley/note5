//Note5 Document class
var Note5Doc = function() {
    date = new Date();
    this.name = date.get8601Date() + ' ' + date.get8601Time();
    this.content = '';
    this.docId = guidGenerator(); 
};

//Note5 Application static class
var Note5 = {
    updateTime: 800,
    localStorageKey: 'Note5.notes',
    instanceId: null,
    currentEmail: null,
    init: function() {
        //window.onerror = this.errorHandler; *** Completely disable error handling until we can figure out how to make it work offline
        this.doc.view = this.view;
        this.view.doc = this.doc;
        
        // Generate or load application unique identifier
        this.getInstanceId();
    
        if(window.localStorage) {
            // Load notes from local storage
            this.doc.loadLocal();
        } else {
            $('#main').html('<b style="color:red">Error:</b> Sorry, your browser does not support saving notes locally. Please upgrade :(');
            $('#login').html('');
            return;
        }
        
        // If there are no notes, create one
        if(this.doc.notes.length < 1) {
            this.cmdNew();
            this.doc.saveCurrent();
        }
        else {
            this.view.refreshSavedArea();
            this.view.refreshNote();
       }
    
        // Resize width on device flip (iOS)
        //window.onorientationchange=this.onresize;
    
        // Resize the note textarea width
        //$(window).resize(this.onresize);
    
        // Force element width update
        //this.onresize();
    
        setTimeout('Note5.view.refreshPage()', Note5.updateTime);

        // Attach main button handlers
        this.setupButtonHandlers();
        
        // Indicate that initialization is complete
        //$('#note').css('background', '#ffc');
        $('#note').removeAttr('disabled');
        
        // Set up vertical auto resize handler
        $('textarea#note').autoResize({});
        
        // Check to see if we're logged in (do this last to minimize loading delay)
        $.get('api/?action=checklogin&instanceId='+Note5.instanceId, function(data) {Note5.setCurrentEmail(data);}, 'html'); 
        
        // TODO: Check for new documents
        
        //CacheHelper.setStatusDiv('#offlineStatus'); 
    },
    
    //Document subclass
    doc: {
        view: null,
        notes: [],
        docIds: [],
        currentNoteIndex: -1,
        setIndex: function(i) { this.currentNoteIndex = i; },
        add: function(doc) { return this.notes.push(doc); },
        getNote: function(i) { return notes[i]; },
        getCurrentNote: function() { 
            if(this.currentNoteIndex >= 0) 
                return this.notes[this.currentNoteIndex];
        },
        updateCurrent: function(content) { 
            this.notes[this.currentNoteIndex].content = content;
            this.view.refreshNote();
        },
    
        findIndexByName: function(name) {
            for(var i = 0; i < this.notes.length; i++) {
                if(this.notes[i].name == name)
                    return i;
            }
            return -1;
        },
        
        findIndexById: function(docId) {
            for(var i = 0; i < this.notes.length; i++) {
                if(this.notes[i].docId == docId)
                    return i;
            }
            return -1;
        },
    
        // Save to local storage (requires HTML5 support)
        saveLocal: function() {
            // Old routine
            /*currentNoteName = ''; 
            if(this.currentNoteIndex >= 0)
                currentNoteName = this.notes[this.currentNoteIndex].name;
            data = { notes: this.notes, currentNoteName: currentNoteName };
            json = JSON.stringify(data);
            window.localStorage.setItem(Note5.localStorageKey, json);
            */
            // New routine
            for(i = 0; i < this.docIds.length; i++) {
                json = JSON.stringify(this.notes[i]);
                window.localStorage.setItem(this.notes[i].docId, json);
            }
            json = JSON.stringify(this.docIds);
            window.localStorage.setItem('Note5.docIds', json);
            window.localStorage.setItem('Note5.currentDocId', this.notes[this.currentNoteIndex].docId);
        },
        
        // Save current document to local storage
        saveCurrent: function() {
            currentNote = this.getCurrentNote();
            json = JSON.stringify(currentNote);
            window.localStorage.setItem(currentNote['docId'], json);
        },
    
        // Load from local storage (requires HTML5 support)
        loadLocal: function() {
            // Old routine
            json = window.localStorage.getItem(Note5.localStorageKey);
            if(json) {
                data = JSON.parse(json);
                if(data) {
                    this.notes = data.notes;
                    currentNoteName = data.currentNoteName;
                    this.currentNoteIndex = this.findIndexByName(currentNoteName);
                    
                    // Update docIs list
                    for(i = 0; i < this.notes.length; i++) {
                        if(!this.notes[i].docId)
                            this.notes[i].docId = guidGenerator(); // Set id if not already set
                        docId = this.notes[i].docId
                        this.docIds.push(docId);
                    }
                }
                
                // Save to new format
                this.saveLocal();
                
                // Delete old format
                window.localStorage.removeItem(Note5.localStorageKey);
                this.notes = [];
                this.currentNoteIndex = -1;
                this.docIds = [];
            }
            
            // New routine
            json = window.localStorage.getItem('Note5.docIds');
            if(json) {
                data = JSON.parse(json);
                if(data) {
                    this.docIds = data;
                    for(i = 0; i < data.length; i++) {
                        json = window.localStorage.getItem(data[i]);
                        if(json) {
                            note = JSON.parse(json);
                            if(note) {
                                this.notes.push(note);
                            }
                        }
                    }
                }
            }
            var currentDocId = window.localStorage.getItem('Note5.currentDocId');
            this.currentNoteIndex = this.findIndexById(currentDocId);
            
            // If it can't find the correct current note, set it to the last
            if(this.currentNoteIndex < 0 && this.notes.length > 0)
                this.currentNoteIndex = (this.notes.length-1);
        }
    },
    
    //View subclass
    view: {
        doc: null,
        updateRunning: false,
    
        // Refresh everything that needs to be updated every updateTime milliseconds
        refreshPage: function(force) {
            if(this.updateRunning) return;
            this.updateRunning = true;
        
            // If text hasn't changed since last update, return
            var noteVal = $('#note').val();
            if(!force && noteVal == this.doc.getCurrentNote().content) {
                setTimeout('Note5.view.refreshPage()', Note5.updateTime);
                this.updateRunning = false;
                return;
            }
        
            // Save note content to doc
            this.doc.updateCurrent(noteVal);
            this.doc.saveCurrent();
        
            // Save all notes locally
            this.doc.saveLocal();
        
            // Update list of saved documents
            this.refreshSavedArea();
        
            //date = new Date();
            //console.log('refreshPage '+date.get8601Date() + '.' + date.get8601Time());
        
            setTimeout('Note5.view.refreshPage()', Note5.updateTime);
            this.updateRunning = false;
        },
    
        //Refresh the 'Saved' tab
        refreshSavedArea: function() {
            var savedList = '<table class="fileList">';
            
            // List documents in "most recently created first" order
            for(var i = (this.doc.notes.length-1); i >= 0; i--) {
                var note = this.doc.notes[i];
                var content = note.content;
                var name = note.name;
                var activeTxt = '';
                if(this.doc.currentNoteIndex == i)
                    activeTxt = ' active';
                if(content.length > 40)
                    content = content.substr(0, 40) + '...';
                savedList += '<tr class="'+activeTxt+'">'+
                /*'<td><button onclick="Note5.cmdRemoveConfirm(\''+name+'\');"  class="icon">' +
                '<img src="images/icon_recycle.png" class="icon" alt="Delete" title="Delete" /></button></td>' +
                '<td><form method="post" action="api/?action=dt" style="display:inline;">' +
                '<input type="hidden" name="fn" value="' + note.name + '">' +
                '<input type="hidden" name="data" value="' + htmlEntities(note.content) + '">' +
                '<button type="submit" class="icon"><img src="images/icon_download.png" class="icon" alt="Download" title="Download" /></button>' +
                '</form></td>' +*/
                '<td onclick="Note5.cmdMakeActive(\''+name+'\');"><div style="width:9.5em;display:inline-block;">'+name+'</div> '+content+'</td></tr>';
            }
            savedList += '</table>'+"\n";
            $('#saved_docs').html(savedList);
        
            // Update 'Saved' icon with # of documents
            var numDocs = this.doc.notes.length;
            if(numDocs == 0) numDocs = '';
            $('#num_saved').html(numDocs);
        },
        
        refreshNote: function() {
            $('#note').val(this.doc.getCurrentNote().content);
            $('#note').keydown(); // resize textarea    
        }
    },
    
    //Command: Create a new note
    cmdNew: function() {
        var newDoc = new Note5Doc();
        this.doc.setIndex(this.doc.add(newDoc)-1);
        this.doc.docIds.push(newDoc.docId);
        this.view.refreshNote();
        this.view.refreshPage(true);
        this.showNote();
        $('#note').focus();
    },
    
    //Command: Make the selected note active
    cmdMakeActive: function(name) {
        var index = this.doc.findIndexByName(name);
        if(index >= 0 ) {
            this.doc.setIndex(index);
            this.doc.saveLocal();
            this.showNote();
            this.view.refreshSavedArea();
            this.view.refreshNote();
            $('#note').focus();
        }
    },
    
    cmdRemoveConfirm: function(name) {
        $( "#dialog-confirm-delete" ).dialog({
            resizable: false,
            //height:'15em',
            modal: true,
            buttons: {
            "Delete": function() {
            $( this ).dialog( "close" );
            Note5.cmdRemove(name);
        },
        "Cancel": function() {
            $( this ).dialog( "close" );
        }
        }
        });    
    },
    
    //Command: Remove the selected note
    cmdRemove: function(name) {
        var oldName = this.doc.getCurrentNote().name;
        removeIndex = this.doc.findIndexByName(name);
        if(removeIndex >= 0) {
            this.doc.notes.splice(removeIndex, 1);
        }
        oldIndex = this.doc.findIndexByName(oldName);
        if(oldIndex >= 0) {
            this.doc.setIndex(oldIndex);
        } else if(this.doc.notes.length) {
            this.doc.setIndex(0);
        } else {
            this.doc.setIndex(-1);
            this.cmdNew();
        }
        this.doc.saveLocal();
        this.view.refreshSavedArea();
        this.view.refreshNote();
    },
    
    showNote: function() {
        $('#main').show();
        $('#saved').hide();
        $('#config').hide();
    },
    
    //Utility functions
    setupButtonHandlers: function() {
        $('#button_saved').click( function() {
            $('#main').hide();
            $('#saved').show();
            $('#config').hide();
            $('saved_docs').focus();
        });
        $('#button_config').click( function() {
            $('#main').hide();
            $('#saved').hide();
            $('#config').show();
        });
        $('#button_new').click( function() {
            Note5.cmdNew();
        })
    },
    
    resetApplication: function() {
        // Reset localstorage
        localStorage.removeItem(Note5.localStorageKey);
        $('#saved_message').html('Application has been reset: '+(new Date()).get8601Time());
    },
    
    //Resize the app window width as necessary
    onresize: function() {
        
        // Width
        var docWidth=window.innerWidth;
        //var docWidth=$('#note_table').width();
        $('#note').css('width', docWidth-32);
    
        // Height
        //var docHeight=window.innerHeight;
        //var navHeight=$('#nav').height();
        //$('#note').css('height', docHeight-navHeight-40);
    },
    
    errorHandler: function(errMsg, errUrl, errLine) {
        var errData = {
                version: note5fileVersion,
                type: -1,
                msg: errMsg,
                url: errUrl,
                line: errLine,
                appCodeName: navigator.appCodeName,
                appName: navigator.appName,
                appVersion: navigator.appVersion,
                cookieEnabled: navigator.cookieEnabled,
                platform: navigator.platform,
                userAgent: navigator.userAgent
        };
    
        if(typeof(errData.msg) != 'string')
            errData.msg = 'Unknown (not string)';
    
        jsonData = JSON.stringify(errData);
    
        data = jsonData.urlEncode();
    
        $.get('api/?action=log&type=-1&data='+data, function(data) {$('#error-return').html(data)} );
    
        /* Quiet errors for now
        $( "#dialog-error" ).dialog({
          resizable: false,
          //height:'15em',
          modal: true,
          buttons: {
            "Argh!": function() {
              $( this ).dialog( "close" );
            }
          }
        });
         */    
    
    },
    
    getInstanceId: function() {
        if(window.localStorage) {
            var data = window.localStorage.getItem('Note5.instanceId');
            if(data) {
                this.instanceId = data;
            }
            else {
                this.instanceId = guidGenerator();
                window.localStorage.setItem('Note5.instanceId', this.instanceId);
            }
            
            $('#login').html('<a href="api/?action=glogin&instanceId='+this.instanceId+'">Sign in</a>');            
        }        
    },
    
    setCurrentEmail: function(email) { 
        Note5.currentEmail = email;
        if(Note5.currentEmail) { 
            $('#login').html(Note5.currentEmail+'<br><a href="api/?action=logout&instanceId='+
                Note5.instanceId+'">Sign out</a>');
        }
    },

    dummy: null
};

