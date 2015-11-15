<div id="editor-menu-wrapper">
    
    <?php if( !$file_inited ): ?>
    
        <div class="middle-sub-wrapper">
            <form action="/?action=upload" method="post" enctype="multipart/form-data">
                <input type="file" name="document" />
                <button id="start-button" type="submit">Start edit</button>
            </form>

        </div>
    
    <?php else: ?>
    
        <div class="middle-sub-wrapper">
            <div id="msg-area"></div>
            <button id="save-button" >Save</button>
            <button id="download-button" >Download</button>
            <button id="cancel-button" >Cancel</button>

        </div>  
    
    <?php endif; ?>

</div>
