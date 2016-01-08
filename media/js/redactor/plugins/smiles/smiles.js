/*
* Плагин смайлов для http://imperavi.com/redactor
*/
if (!RedactorPlugins) var RedactorPlugins = {};

/* путь к папке с смайлами */
var path='/media/js/redactor/plugins/smiles/images/';

RedactorPlugins.smiles = {
	init: function()
	{
	    /* смайлы */
		var smiles = ['1.gif','2.gif','3.gif','4.gif','5.gif','6.gif','7.gif','8.gif','9.gif','10.gif',
		               '11.gif','12.gif','13.gif','14.gif','15.gif','16.gif','17.gif','18.gif','19.gif',
					   '20.gif','21.gif','22.gif','23.gif','24.gif','25.gif','26.gif','27.gif','28.gif',
					   '29.gif','30.gif','31.gif','32.gif'];
		var that = this;
		var dropdown = {};
		
		

		$.each(smiles, function(i, s)
		{
			dropdown['s' + i] = { title: '<img src="'+path+s+'" class="img">', className: 'smilelink', callback: function() { that.setSmile(s); } };
		});

		/*
		* Добавляем кнопку на панель, иконка для кнопки прописана в smiles.css
		*/
		this.addBtn( 'smile', 'Smiles', false, dropdown);

	},
    /*
    * класс .img нужен чтобы для картинок смайлов не срабатывала встроенная функция вывода модального окна, растягивания, удаление картинок
     */
	setSmile: function(name)
	{
       this.execCommand('inserthtml', '<img src="'+path+name + '" class="img">');
        //this.execRun('inserthtml', '<img src="'+path+name + '" class="img">');
	}
};