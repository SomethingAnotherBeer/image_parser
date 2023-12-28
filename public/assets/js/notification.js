class Notification
{

	constructor() {
		this.body = document.body;
	}


	 renderErrorNotification(notification_text) {
		const notificationNode = this.__getNotificationNode(notification_text);
		notificationNode.classList.add('error');
		this.__renderNotificationNode(notificationNode);

	}


	renderSuccessNotification(notification_text) {
		const notificationNode = this.__getNotificationNode(notification_text);
		notificationNode.classList.add('success');
		this.__renderNotificationNode(notificationNode);
	}


    removeNotification() {
        this.__removePreviousNotification();
    }

	  __getNotificationNode(notification_text) {
		const notificationNode = document.createElement('div');
		const notificationTextNode = document.createElement('div');

		notificationNode.className = 'notification';
		notificationTextNode.className = 'notification-text';

		notificationTextNode.innerText = notification_text;
		notificationNode.append(notificationTextNode);

		return notificationNode;

	}

	__renderNotificationNode(notificationNode) {
		this.__removePreviousNotification();
		
		this.body.prepend(notificationNode);
		notificationNode.style.display = 'block';

	}


	__removePreviousNotification() {
		const notificationNode = this.body.querySelector('.notification');

		if (notificationNode) {
			notificationNode.remove();
		}
	}


}