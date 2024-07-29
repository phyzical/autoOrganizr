# autoOrganizr Organizr Plugin

| :exclamation: Important                                                                                                                             |
| :-------------------------------------------------------------------------------------------------------------------------------------------------- |
| To add this plugin to Organizr, please add <https://github.com/phyzical/Organizr-Plugins> to the Plugins Marketplace within your Organizr instance. |

Go to the settings;
Set the docker proxy url if not the same as the default `http://docker:2375`
By default runs on cron once an hour, can be changed in settings

otherwise

To run after installing simply click the new menu item
![alt text](images/menu_item.png)

Configuration is just a matter of using the following labels:
| Label                  | Description                                          | Default                                                                             | Required                                  |
| ---------------------- | ---------------------------------------------------- | ----------------------------------------------------------------------------------- | ----------------------------------------- |
| organizr.tab.enabled   | The bare minimum to start creating a tab             | false                                                                               | true                                      |
| organizr.tab.image     | Image to use for tab                                 | defaults to `net.unraid.docker.icon` otherwise `null`                               | only if `net.unraid.docker.icon` isnt set |
| organizr.tab.url       | Url to use for tab                                   | If domain is set defaults to `https://{container_name}.{DOKMAIN}`, otherwise `null` | Only if domain isnt set                   |
| organizr.tab.name      | Name for the tab                                     | {container_name}                                                                    | false                                     |
| organizr.tab.local_url |                                                      | defaults to `http//{container_name}:{FIRST_EXPOSED_PORT}`                           | false                                     |
| organizr.tab.group_id  | ID of the group to be added to                       | `null`                                                                              | false                                     |
| organizr.tab.default   | If its a default                                     | `null`                                                                              | false                                     |
| organizr.tab.type      | The type of the tab, `organizr, iframe or newWindow` | `iframe`                                                                            | false                                     |
| organizr.tab.order     | Order of the tab                                     | `null`                                                                              | false                                     |

Assumes usage of the [docker proxy server](https://github.com/linuxserver/docker-socket-proxy)

TODO:
Support docker socket bind?
