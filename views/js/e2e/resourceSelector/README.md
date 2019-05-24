# About these tests

The tests in this folder are for the **resourceSelector** component in TAO CE.

Please note that the resourceSelector looks and behaves differently from the **jsTree** component which is loaded by default in (e.g.) TAO 3.3.0-sprint101.

To install the resourceSelector, run the following command from your instance root:

```
php index.php "\\oat\\tao\\scripts\\install\\SetResourceSelector"
```

If using a Docker container to run TAO:

```
docker exec -it {taocontainername} php index.php "\\oat\\tao\\scripts\\install\\SetResourceSelector"
```

You should find the configuration in `/config/tao/client_lib_config_registry.conf.php` has been updated with a new value of `treeProvider`.
