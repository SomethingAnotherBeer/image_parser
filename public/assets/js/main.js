


const parse_url = '/images/parse';
const task_url = '/images/task';
const get_parsed_url = 'images/getparsed';
const headers = {'Content-type': 'application/json', 'accept': 'json'};

function renderImages(images) {

	const images_block_node = document.querySelector('.parsed-images');
	const info_block = document.querySelector('.info');
	images_block_node.innerHTML = '';
	console.log(images);
	const sources = images.map((image_data) => image_data['src']);
	let size = images.reduce((sum, image_data) => sum+= image_data['size'], 0);
	size = Number(size).toFixed(2);

	let currentImageNode = null;

	for (let source of sources) {
		currentImageNode = getImageItem(source);
		images_block_node.append(currentImageNode);
	}

	info_block.innerText = `Суммарный размер загруженных изображений составляет ${size} МБ`;


}




function getImageItem(image_src) {
	const imageItemNode = document.createElement('div');
	const imageNode = document.createElement('img');
	imageItemNode.className = 'image';
	imageNode.src = image_src;
	imageItemNode.append(imageNode);

	return imageItemNode;

}


function setParseTask(url_value) {

	const request_body = {page_url: url_value};

	return fetch(parse_url, {
		method: 'POST',
		headers: headers,
		body: JSON.stringify(request_body)
	})
}


function getTaskStatus(task_key) {
	const task_path = task_url + `/${task_key}/status`;

	return fetch(task_path);

}



function checkStatusCode(response_params, accepted_code) {
	if (response_params.status_code !== accepted_code) {
		throw new Error(response_params.content);
	}
}




const parseBtn = document.getElementById('parse_btn');



parseBtn.addEventListener('click', (e) => {

	const notificator = new Notification();
	const parsedUrlInput = document.getElementById('page_url');
	const url_value = parsedUrlInput.value.trim();
	console.log(url_value);
	
	setParseTask(url_value)
		.then((response) => response.json().then((data) => {return {status_code: response.status, content: data}}))
		.then((data) => {
			console.log(data);
			checkStatusCode(data, 201);
			const task_key = data.content['task_key']
			console.log(task_key);

			notificator.renderSuccessNotification("Пожалуйста подождите");

			return new Promise((resolve, reject) => {
				let interval = setInterval(() => {
					getTaskStatus(task_key)
					.then((response) => response.json().then((data) => {return {status_code: response.status, content: data}}))
					.then((data) => {

						if (200 !== data.status_code) {
							clearInterval(interval);
							reject(data.content);
						}
						
						if (data.content['current_status']) {
							clearInterval(interval);
							resolve(data.content);
						}
					})
				}, 2000);
			});
		})
		.then(() => {
			return fetch(get_parsed_url + `?page_url=${url_value}`);
		})
		.then((response) => response.json().then((data) => {return {status_code: response.status, content: data}}))
		.then((data) => {
			checkStatusCode(data, 200);
			notificator.renderSuccessNotification("Данные успешно загружены");
			setTimeout(() => notificator.removeNotification(), 2000);
			console.log(data);

			renderImages(JSON.parse(data.content['prepared_images']));
		})
		.catch((error) => {
			console.log(error);
			notificator.renderErrorNotification(e.message);
			setTimeout(() => notificator.removeNotification(), 5000);
		})


})