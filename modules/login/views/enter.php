<div>
    <form method="post">
        <!--<input type="hidden" name="token_auth" value="">-->
        <div class="modal-header">
            <a href="<?= Url::site('/') ?>" class="close" data-dismiss="modal" aria-hidden="true">&times;</a>
            <h4 class="form-signin-heading">Укажите данные для входа</h4>
        </div>
        <div class="modal-body">
            <?php if ($error): ?>
            <div class="alert alert-error"><strong>Ошибка!</strong>Имя пользователя или пароль не верны
            </div><?php endif ?>
            Имя пользователя:
            <input type="text" name="username" class="input-block-level" value="<?=$username?>" required="required">
            Пароль:
            <input type="password" name="password" class="input-block-level" required>
            <?php if ($captcha): ?>
            <img src="<?=Url::media('/captcha/default')?>" width="150" height="50" alt="Captcha" class="captcha_old"/>
            <input type="text" name="captcha" required style="margin: 0; width: 147px; height: 42px; font-size:35px">
            <a onclick="reload()" style="cursor: pointer;"><img src="<?=Url::media('media/theme/refresh.png')?>" width="16px" height="16px"></a>
            <script type="text/javascript">
                function reload(){
                    id=Math.floor(Math.random()*1000000);
                    $("img.captcha_old").attr("src","<?=Url::media('/captcha/default?id=')?>"+id);
                }
            </script>
            <?php endif ?>
        </div>
        <div class="modal-footer">
            <div class="pull-left style-button" id="uLogin"
                 x-ulogin-params="display=panel;fields=first_name,last_name,photo,photo_big;optional=phone,email;providers=vkontakte,facebook,mailru,odnoklassniki,twitter,yandex,google;hidden=;redirect_uri=<?= URL::site('login/enter', true) ?>"></div>
            <script src="http://ulogin.ru/js/ulogin.js"></script>
            <button class="btn btn-large btn-primary" type="submit">Войти</button>
        </div>
    </form>
</div>