<div id="message" class="alert alert-<?php echo $message->type; ?>">
    <?php if( is_array( $message->message ) ): ?>
        <?php foreach( $message->message as $msg ): ?>
            <?php echo $msg; ?><br>
        <?php endforeach;?>
    <?php else: ?>
        <?php echo $message->message; ?>
    <?php endif; ?>
</div>
