//Note5 Document class
var Note5Doc = function() {
    date = new Date();
    this.name = date.get8601Date() + ' ' + date.get8601Time();
    this.content = '';
    this.docId = guidGenerator();
    this.lastWrite = null;
    this.isDirty = true;
};

//Note5 Application static class
var Note5 = {
    updateTime: 4950,
    localStorageKey: 'Note5.notes',
    lastWrite: -1,
    instanceId: null,
    currentEmail: '',
    init: function() {
        //window.onerror = this.errorHandler; *** Completely disable error handling until we can figure out how to make it work offline
        this.doc.view = this.view;
        this.view.doc = this.doc;
        
        // Generate or load application unique identifier
        this.getInstanceId();
    
        if('localStorage' in window && window['localStorage'] !== null) { // http://diveintohtml5.org/detect.html
            // Load notes from local storage
            this.doc.loadLocal();
        } else {
            $('#main').html('<b style="color:red">Error:</b> Sorry, your browser does not support saving notes locally. Please upgrade :(');
            $('#login').html('');
            return;
        }
        
        // Set up note change handler
        $('textarea#note')
            .unbind('.noteChange')
            .bind('keyup.noteChange', this.view.noteChanged)
            .bind('keydown.noteChange', this.view.noteChanged)
            .bind('change.noteChange', this.view.noteChanged);

        // Attach main button handlers
        this.setupButtonHandlers();
        
        // If there are no notes, create one
        if(this.doc.notes.length < 1) {
            this.cmdNew();
        }
    
        // Force a refresh immediately
        Note5.view.refreshPage(true);        
        setTimeout('Note5.view.refreshPage()', this.updateTime);
        
        $('#loading').hide();
        $('#container').show();
        
        // Set up vertical auto resize handler
        if(mobileMode) {
            $('textarea#note').autoResize({});
        } else {
            this.onresizeDesktop();
            $(window).resize(function() {
                Note5.onresizeDesktop();
              });            
        }
        
        var currentNote = this.doc.getCurrentNote();
        if(currentNote) {
            this.cmdMakeActive(currentNote.docId);
        }
        
        // Indicate that initialization is complete
        $('#note').removeAttr('disabled');
        
        // Check to see if we're logged in (do this last to minimize loading delay)
        $.get('api/?action=checklogin&instanceId='+Note5.instanceId, function(data) {Note5.setLoggedIn(data);}, 'html'); 
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
        getNoteById: function(docId) {
            var index = this.findIndexById(docId);
            if(index >= 0) {
                return this.notes[index];
            }
            
            return null;
        },
        getCurrentNote: function() { 
            if(this.currentNoteIndex >= 0) 
                return this.notes[this.currentNoteIndex];
            return null;
        },
        updateCurrent: function(content) {
            if(this.currentNoteIndex >= 0) {
                this.notes[this.currentNoteIndex].content = content;
                //this.view.refreshNote();
            }
        },
    
        // Used by old loadLocal routine
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
            // Old routine (for reference)
            /*currentNoteName = ''; 
            if(this.currentNoteIndex >= 0)
                currentNoteName = this.notes[this.currentNoteIndex].name;
            data = { notes: this.notes, currentNoteName: currentNoteName };
            json = JSON.stringify(data);
            localStorage.setItem(Note5.localStorageKey, json);
            */
            // New routine
            for(i = 0; i < this.docIds.length; i++) {
                json = JSON.stringify(this.notes[i]);
                localStorage.setItem(this.notes[i].docId, json);
            }
            json = JSON.stringify(this.docIds);
            localStorage.setItem('Note5.docIds', json);
            localStorage.setItem('Note5.currentDocId', this.notes[this.currentNoteIndex].docId);
            localStorage.setItem('Note5.lastWrite', Note5.lastWrite);
        },
        
        // Save current document to local storage
        saveCurrent: function() {
            currentNote = this.getCurrentNote();
            json = JSON.stringify(currentNote);
            localStorage.setItem(currentNote['docId'], json);
            localStorage.setItem('Note5.currentDocId', this.notes[this.currentNoteIndex].docId);
        },
    
        // Load from local storage (requires HTML5 support)
        loadLocal: function() {
            // Old routine
            json = localStorage.getItem(Note5.localStorageKey);
            if(json) {
                data = JSON.parse(json);
                if(data) {
                    this.notes = data.notes;
                    currentNoteName = data.currentNoteName;
                    this.currentNoteIndex = this.findIndexByName(currentNoteName);
                    
                    // Update docId list
                    for(i = 0; i < this.notes.length; i++) {
                        if(!this.notes[i].docId)
                            this.notes[i].docId = guidGenerator(); // Set id if not already set
                        docId = this.notes[i].docId;
                        this.docIds.push(docId);
                    }
                }
                
                // Save to new format
                this.saveLocal();
                
                // Delete old format
                localStorage.removeItem(Note5.localStorageKey);
                this.notes = [];
                this.currentNoteIndex = -1;
                this.docIds = [];
            }
            
            // New routine
            json = localStorage.getItem('Note5.docIds');
            if(json) {
                var data = JSON.parse(json);
                if(data) {
                    this.docIds = data;

                    for(i = 0; i < data.length; i++) {
                        var json = localStorage.getItem(data[i]);
                        if(json) {
                            var note = JSON.parse(json);
                            if(note) {
                                // Upgrade old format notes
                                if(note.docId == undefined) {
                                    note.docId = guidGenerator();
                                }
                                if(note.lastWrite == undefined) {
                                    note.lastWrite = null;
                                }
                                if(note.isDirty == undefined) {
                                    note.isDirty = true;
                                }
                                
                                this.notes.push(note);
                            }
                        }
                    }
                }
            }
            var currentDocId = localStorage.getItem('Note5.currentDocId');
            var lastWrite = localStorage.getItem('Note5.lastWrite');
            if(lastWrite)
                Note5.lastWrite = lastWrite;
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
        noteChangedRunning: false,
        pageDirty: false,
        
        // Called when the note text area is changed
        noteChanged: function() {
            if(this.noteChangedRunning) return;
            this.noteChangedRunning = true;

            var noteVal = $('#note').val();

            var currentNote = Note5.doc.getCurrentNote();
            if(currentNote == null || noteVal == currentNote.content) {
                this.noteChangedRunning = false;
                return;
            }
            
            // Mark note changed
            currentNote.isDirty = true;
            
            // Save contents to memory
            Note5.doc.updateCurrent(noteVal);
        
            Note5.view.pageDirty = true;
            $('#status_saving').show();
            
            this.noteChangedRunning = false;
        },
    
        // Refresh everything that needs to be updated every updateTime milliseconds
        refreshPage: function(force) {
            if(this.updateRunning) return;
            this.updateRunning = true;
            
            // If text hasn't changed since last update, return
            var noteVal = $('#note').val();
            if(!force && !this.pageDirty) {
                setTimeout('Note5.view.refreshPage()', Note5.updateTime);
                this.updateRunning = false;
                return;
            }
        
            // Save note content to localStorage
            this.doc.saveCurrent();
            this.pageDirty = false;
            $('#status_saving').hide();
            
            // Update list of saved documents
            this.refreshSavedArea();
            
            // If we are logged in, start the sync process
            if(Note5.currentEmail.length) {
                $('#button_sync').hide();
                $('#status_syncing').show();
                
                // ServerToLocal API update
                $.get('api/?action=stl&i='+Note5.instanceId+'&llw='+Note5.lastWrite, Note5.view.serverToLocalProcess);
            }
            
            //date = new Date();
            //console.log('refreshPage '+date.get8601Date() + '.' + date.get8601Time());
        
            setTimeout('Note5.view.refreshPage()', Note5.updateTime);
            this.updateRunning = false;
        },
        
        serverToLocalProcess: function(data) {
            //$('#last-write').html(Note5.lastWrite);
            //$('#status-message').append(data+'<br>');
            
            try {
                serverData = JSON.parse(data);
            } catch(err) {
                serverData = null;
            }
            
            // Cancel sync if we don't get valid JSON
            if(!serverData) {
                $('#status_syncing').hide();
                
                // Re-check to see if we're logged in
                $.get('api/?action=checklogin&instanceId='+Note5.instanceId, function(data) {Note5.setLoggedIn(data);}, 'html'); 
                
                return;
            }
            
            var oldDocId = Note5.doc.getCurrentNote().docId;
            
            var lastWriteServer = serverData['lws'];
            var newLastWriteServer = serverData['nlws'];
            var oldDocs = serverData['oldDocs'];
            var newDocs = serverData['newDocs'];
            
            var deleteList = [];
            var updateList = [];
            
            // Loop through new server notes for ones we don't have yet
            for(i = 0; i < newDocs.length; i++) {
                var doc = newDocs[i];
                
                // If we don't have a new document locally, add it
                if(jQuery.inArray(doc['doc_id'], Note5.doc.docIds) == -1) {
                    var newDoc = new Note5Doc();
                    newDoc.docId = doc['doc_id'];
                    newDoc.name = doc['name'];
                    newDoc.content = doc['content'];
                    newDoc.lastWrite = doc['last_write'];
                    Note5.doc.add(newDoc);
                    Note5.doc.docIds.push(newDoc.docId);
                }
            }
            
            // Loop through old server notes
            for(i = 0; i < oldDocs.length; i++) {
                var doc = oldDocs[i];
                
                // If we don't have an old document locally, it should be deleted from the server
                if(jQuery.inArray(doc['doc_id'], Note5.doc.docIds) == -1) {
                    deleteList.push(doc['doc_id']);
                }
            }
            
            var deleteLocalList = []
            
            // Loop through all local notes
            for(i = 0; i < Note5.doc.docIds.length; i++) {
                var note = Note5.doc.notes[i];
                
                // If our note is out of date, and we find a match, update our copy
                if(note.isDirty == false && note.lastWrite < lastWriteServer) {
                    var found = false;
                    for(j = 0; j < newDocs.length; j++) {
                        doc = newDocs[j];
                        if(note.docId == doc['doc_id']) {
                            found = true;
                            if(note.lastWrite < doc['last_write']) {
                                Note5.doc.notes[i].name = doc['name'];
                                Note5.doc.notes[i].content = doc['content'];
                                Note5.doc.notes[i].lastWrite = doc['last_write'];
                            }
                        }
                    }
                    
                    // Check the old docs array too
                    for(j = 0; j < oldDocs.length; j++) {
                        doc = oldDocs[j];
                        if(note.docId == doc['doc_id']) {
                            found = true;
                        }
                    }
                    
                    // If the note no longer exists on the server, delete it locally
                    if(!found) {
                        deleteLocalList.push(note.docId);
                    }
                } else {
                    // Figure out if we need to send the file to the server
                    if(note.isDirty) {
                        
                        var found = false;
                        for(j = 0; j < newDocs.length; j++) {
                            doc = newDocs[j];
                            if(note.docId == doc['doc_id']) {
                                found = true;
                                // If the server doc is old, update it
                                if(doc['last_write'] < note.lastWrite ) {
                                    note.lastWrite = newLastWriteServer;
                                    updateList.push(note);
                                    note.isDirty = false;
                                }
                            }
                        }
                        
                        // If the server doesn't have the doc, add it to the update list
                        if(!found) {
                            note.lastWrite = newLastWriteServer;
                            updateList.push(note);
                            note.isDirty = false;
                        }
                    }
                }
            }
            
            if(deleteLocalList.length > 0) {
                if(confirm("Sync is about to delete "+deleteLocalList.length+
                        " document(s). This cannot be undone.\n\nPress Ok to confirm.")) {
                    
                    // Delete old local documents not found on server
                    for(i = 0; i < deleteLocalList.length; i++) {
                        docId = deleteLocalList[i];
                        removeIndex = Note5.doc.findIndexById(docId);
                        if(removeIndex >= 0) {
                            Note5.doc.notes.splice(removeIndex, 1);
                            Note5.doc.docIds.splice(removeIndex, 1);
                        }
                        localStorage.removeItem(docId);
                    }
                }
            }
            
            var jsonUp = JSON.stringify(updateList);
            var jsonDel = JSON.stringify(deleteList);

            Note5.lastWrite = newLastWriteServer;
            
            oldIndex = Note5.doc.findIndexById(oldDocId);
            if(oldIndex >= 0) {
                Note5.doc.setIndex(oldIndex);
            } else if(Note5.doc.notes.length) {
                Note5.doc.setIndex(0);
            } else {
                Note5.doc.setIndex(-1);
                Note5.cmdNew();
            }
            
            Note5.doc.saveLocal(); // Persist docIdList
            Note5.view.refreshSavedArea();

            $.post('api/?action=lts', {i: Note5.instanceId, lslw: newLastWriteServer, up: jsonUp, del: jsonDel}, 
                function(data) {
                    //$('#last-write').html(Note5.lastWrite);                
                    //$('#status-message').append(data+'<br>'); 
                    $('#status_syncing').hide();
                    $('#button_sync').show();
                } );
        },
    
        //Refresh the 'Saved' tab
        refreshSavedArea: function() {
            var savedList = '<table class="fileList">';
            
            // List documents in "most recently created first" order
            // TODO: Sort by name? Otherwise sorting gets mixed up with syncing
            for(var i = (this.doc.notes.length-1); i >= 0; i--) {
                var note = this.doc.notes[i];
                var content = note.content;
                var name = note.name;
                var docId = note.docId;
                var activeTxt = '';
                if(this.doc.currentNoteIndex == i)
                    activeTxt = ' active';
                var maxLength = 32;
                if(content.length > maxLength)
                    content = content.substr(0, maxLength) + '...';
                savedList += '<tr id="'+docId+'" class="'+activeTxt+'">'+
                '<td class="fileName" onclick="Note5.cmdMakeActive(\''+docId+'\');"><div style="width:9.5em;display:inline-block;"><b>'+name+'</b></div> '+content+'</td>' +
                '<td class="button">'+
                '<div id="button_saved" class="button-mobile" onclick="Note5.cmdRemoveConfirm(\''+docId+'\');">' +
                '<img src="images/gnome_delete.png" class="icon" alt="Remove" title="Remove" /></td>' +
                /*'<td class="button"><form method="post" action="api/?action=dt" style="display:inline;">' +
                '<input type="hidden" name="fn" value="' + note.name + '">' +
                '<input type="hidden" name="data" value="' + htmlEntities(note.content) + '">' +
                '<button type="submit" class="icon"><img src="images/icon_download.png" class="icon" alt="Download" title="Download" /></button>' +
                '</form></td>' +*/
                '</tr>';
            }
            savedList += '</table>'+"\n";
            $('#saved_docs').html(savedList);
        
            // Update 'Saved' icon with # of documents
            //var numDocs = this.doc.notes.length;
            //if(numDocs == 0) numDocs = '';
            //$('#num_saved').html(numDocs);
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
        this.doc.saveLocal(); // Update docIdList
        this.view.refreshPage(true);
        this.cmdMakeActive(newDoc.docId);
    },
    
    //Command: Make the selected note active
    cmdMakeActive: function(docId) {
        var currentNote = this.doc.getCurrentNote(); 
        if(currentNote) {
            $('#'+currentNote.docId).removeClass('active');
        }
        
        var index = this.doc.findIndexById(docId);
        if(index >= 0 ) {
            this.doc.setIndex(index);
            $('#'+docId).addClass('active');
            this.showNote();
            this.view.refreshNote();
            this.view.refreshPage();
            this.doc.saveCurrent();
            $('#note').focus();
            $('#note').putCursorAtEnd();
        }
    },
    
    cmdRemoveConfirm: function(docId) {
        if(confirm("This item will be permanently deleted.\n\nPress Ok to confirm.")) {
            Note5.cmdRemove(docId);
        }    
    },
    
    //Command: Remove the selected note
    cmdRemove: function(docId) {
        var oldDocId = this.doc.getCurrentNote().docId;
        removeIndex = this.doc.findIndexById(docId);
        if(removeIndex >= 0) {
            this.doc.notes.splice(removeIndex, 1);
            this.doc.docIds.splice(removeIndex, 1);
        }
        oldIndex = this.doc.findIndexById(oldDocId);
        if(oldIndex >= 0) {
            this.doc.setIndex(oldIndex);
        } else if(this.doc.notes.length) {
            this.doc.setIndex(0);
        } else {
            this.doc.setIndex(-1);
            this.cmdNew();
        }
        this.doc.saveLocal(); // Update docIdList
        this.view.refreshPage(true);
        localStorage.removeItem(docId);
    },
    
    showNote: function() {
        if(mobileMode) $('#main').show();
        if(mobileMode) $('#saved').hide();
        if(mobileMode) $('#config').hide();
    },
    
    //Utility functions
    setupButtonHandlers: function() {
        $('#button_saved').click( function() {
            // Home
            Note5.view.refreshPage();
            if(mobileMode) $('#main').hide();
            $('#saved').show();
            if(!mobileMode) $('#main_table').show();
            if(!mobileMode) $('#main').show();
            $('#config').hide();
            $('saved_docs').focus();
        });
        $('#button_config').click( function() {
            if(!mobileMode) $('#main_table').hide();
            $('#main').hide();
            $('#saved').hide();
            $('#config').show();
        });
        $('#button_new').click( function() {
            Note5.cmdNew();
        });
        $('#button_sync').click( function() {
            Note5.view.refreshPage(true); 
        });
        $('#button_login').click( function() {
            window.location = 'api/?action=glogin&instanceId='+Note5.instanceId;        
        });
    },
    
    resetApplication: function() {
        // Reset localstorage
        localStorage.clear();
        $('#saved_message').html('Application has been reset: '+(new Date()).get8601Time());
    },
    
    importOld: function() {
        var json = $('#import_data').val();
        if(json) {
            data = JSON.parse(json);
            if(data) {
                for(i = 0; i < data.length; i++) {
                    var newDoc = new Note5Doc();
                    newDoc.name = data[i].name;
                    newDoc.content = data[i].content;
                    Note5.doc.setIndex(Note5.doc.add(newDoc)-1);
                    Note5.doc.docIds.push(newDoc.docId);
                }
            }
            
            Note5.doc.saveLocal(); // Update docIdList
            Note5.view.refreshPage(true);
        }
        
        $('#import_old').hide();
        $('#button_saved').click();        
    },
    
    onresizeDesktop : function() {
        var menuHeight = $('#menu_bar').height();
        var viewportHeight = $(window).height();
        var elementHeight = viewportHeight - menuHeight - 100;
        $('#saved').height(elementHeight);
        $('#note').height(elementHeight-16);
        $('#saved_td').height(elementHeight);
        $('#note_td').height(elementHeight-16);
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
    
    setLoggedIn: function(email) {
        oldEmail = Note5.currentEmail; 
        Note5.currentEmail = email;
        if(Note5.currentEmail) { 
            $('#login').html(Note5.currentEmail+' | <a href="api/?action=logout&instanceId='+
                Note5.instanceId+'">Sign out</a>');
            
            // Hide the login button
            $('#button_login').hide();
            
            // Show the sync button
            $('#button_sync').show();
            
            if(oldEmail != Note5.currentEmail) {
                // Do a sync, if the login status has changed
                Note5.view.refreshPage(true);
            }
        } else {
            $('#login').html('<a href="api/?action=glogin&instanceId='+this.instanceId+'">Sign in</a>');            

            // Hide the sync button
            $('#button_sync').hide();
            
            // Show the login button
            $('#button_login').show();
        }
    },

    dummy: null
};

