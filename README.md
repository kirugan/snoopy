# snoopy
Часть библиотеки позволяющей искать похожего пользователя в соцсетях

Благодаря декораторам можно легко дополнять логику базового api-класса.
Пример декораторов:
LogVKApiDecorator - логгирует вызовы к api
TimeoutVKApiDecorator - игнорирование ошибки api.vk.com (flood control) и автоматический перезапуск этого запроса в апи вк
