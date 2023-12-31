## image_parser

Это прототип приложения для парсинга изображений со сторонних ресурсов. Многие аспекты требуют доработки.  

Клиент-серверное взаимодействие в рамках получения изображений со стороннего ресурса осуществляется по следующей схеме:  
1. Осуществляется POST запрос по адресу /images/parse с телом запроса, имеющим единственный параметр page_url, представляющий собой url, с которого необходимо получить изображения.
2. Далее, в очередь ставится задача на парсинг с уникальным идентификатором, который возвращается клиенту.
3. Клиент каждые две секудны запрашивает статус выполнения задачи по адресу /images/task/{task_key}/status.
4. Если статус выполнения задачи равен true, то осуществляется GET запрос по адресу images/getparsed??page_url={parsed_url}, которому в качестве единственного параметра page_url передается адрес ранее переданного на парсинг ресурса.
5. Все изображения динамически рендерятся на клиенте с выводом общего размера всех изображений в мегабайтах.

На сервере в это время осуществляется:  
1. После первичного запроса клиента на парсинг, создается запись в redis с уникальным идентификатором задачи и ее статусом, равным false
2. Задача, вместе с ранее созданным уникальным идентификатором, передается в очередь, обработчик которой при выполнении данной задачи осуществляет получение всех изображений со стороннего ресурса и выясняет размер каждого изображения.
3. Статус задачи по переданному ранее идентификатору устанавливается в true и дополнительно создается запись, ключом которой является переданный задаче url, а значением - json строка обработанных изображений. 
