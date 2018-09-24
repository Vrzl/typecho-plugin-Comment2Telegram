<?php

/**
 * Telegram 推送评论通知
 *
 * @package Comment2Telegram
 * @author Vrzl
 * @version 1.0.0
 * @link https://github.com/Vrzl/typecho-plugin-Comment2Telegram
 */
class Comment2Telegram_Plugin implements Typecho_Plugin_Interface
{
	/**
	 * 激活插件方法,如果激活失败,直接抛出异常
	 *
	 * @access public
	 * @return void
	 * @throws Typecho_Plugin_Exception
	 */
	public static function activate()
	{
		Typecho_Plugin::factory('Widget_Feedback')->finishComment = array('Comment2Telegram_Plugin', 'callback_send');
		Typecho_Plugin::factory('Widget_Comments_Edit')->finishComment = array('Comment2Telegram_Plugin', 'callback_send');

		return _t('请配置此插件的 IFTTT Key, 以使您的 Telegram 推送生效');
	}

	/**
	 * 禁用插件方法,如果禁用失败,直接抛出异常
	 *
	 * @static
	 * @access public
	 * @return void
	 * @throws Typecho_Plugin_Exception
	 */
	public static function deactivate()
	{
	}

	/**
	 * 获取插件配置面板
	 *
	 * @access public
	 * @param Typecho_Widget_Helper_Form $form 配置面板
	 * @return void
	 */
	public static function config(Typecho_Widget_Helper_Form $form)
	{
		$callback_token = new Typecho_Widget_Helper_Form_Element_Text('callback_token', NULL, NULL, _t('callback_token'), _t('需要输入指定token'));
		$form->addInput($callback_token->addRule('required', _t('您必须填写一个正确的token')));
	}

	/**
	 * 个人用户的配置面板
	 *
	 * @access public
	 * @param Typecho_Widget_Helper_Form $form
	 */
	public static function personalConfig(Typecho_Widget_Helper_Form $form)
	{
	}

	/**
	 * 通知发送
	 * @param $comment
	 * @return mixed
	 */
	public static function callback_send($comment)
	{
		$options = Typecho_Widget::widget('Widget_Options');

		$callback_url = 'https://maker.ifttt.com/trigger/comment2telegram/with/key/' . $options->plugin('Comment2Telegram')->callback_token;

		$text = "{$comment->author} 在您的文章《{$comment->title}》中提交了评论：\n\n{$comment->text}\n\n回复：\n{$comment->permalink}";

		$post_data = json_encode(
			array(
				"value1" => $text
			)
		);

		self::request($callback_url, $post_data);
	}

	private static function request($url, $data = null, $referer = null)
	{
		$ch = curl_init();
		curl_setopt_array(
			$ch,
			array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => [
					'Content-Type: application/json'
				],
				CURLOPT_REFERER => $referer
			)
		);
		if ($data) {
			curl_setopt_array(
				$ch,
				array(
					CURLOPT_POST => true,
					CURLOPT_POSTFIELDS => $data
				)
			);
		}
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}
}
