<div class="modal">
    <?php if (!isset($ok)):?>
    <form method="post">
        <!--<input type="hidden" name="token_auth" value="">-->
        <input type="hidden" name="id" value="<?=$id ?>">
        <div class="modal-header">
            <a href="<?=Url::site('/')?>" class="close" data-dismiss="modal" aria-hidden="true">&times;</a>
            <h4 class="form-signin-heading">Изменение пароля</h4>
        </div>
        <div class="modal-body">
            <?php if (isset($errors)):?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $ee):?>
                        <strong>Ошибка!</strong>
                        <?=$ee?><br>
                    <?php endforeach;?>
                </div>
            <?php endif;?>
            Введите новый пароль:
            <input type="password" name="password" class="input-block-level" required="required"/>
            Введите его ещё раз для надёжности:
            <input type="password" name="password_confirm" class="input-block-level" required="required"/>
        </div>
        <div class="modal-footer">
            <button class="btn btn-large btn-primary" type="submit">Изменить пароль</button>
        </div>
    </form>
    <?php else:?>
    <div class="modal-header">
        <a href="<?=Url::site('/')?>" class="close" data-dismiss="modal" aria-hidden="true">&times;</a>
        <h4 class="form-signin-heading">Восстановление пароля</h4>
    </div>
    <div class="modal-body">
        Пароль успешно изменен. Теперь вы можете войти на сайт с новым паролем.
    </div>
    <?php endif;?>
</div>