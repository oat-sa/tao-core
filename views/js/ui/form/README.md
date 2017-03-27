### TODO:
- Move form styles to ui/form/form.scss
- Incorporate fieldset and legend
- Create library of reusable validators
- Reduce api to a minimum for consistent usage
	- Could promisify
- Alphabetize (or create a consistent way to place properties)


### Users resource
@describe [GET]   tao/users/desc?uri
@add      [POST]  tao/users/add
@edit     [PATCH] tao/users/edit?uri


### Users pages
  TITLE                 STRUCTURE  EXT  SECTION       CONTROLLER
  Manage users          users      tao  list_users
* Add a user            users      tao  add_user      .../users/add
* Edit a user           users      tao  edit_user     .../?
  Manage roles          users      tao  manage_roles
  Manage Access Rights  users      tao  manage_acl


### Users schema
  KEY        VALUE/RDFS                                              TYPE
  classUri   http://www.tao.lu/Ontologies/TAO.rdf#User               str
  uri/id     http://taoplatform/data.rdf#i1490...                    str
  label      http://www.w3.org/2000/01/rdf-schema#label              str
  firstName  http://www.tao.lu/Ontologies/generis.rdf#userFirstName  str
  lastName   http://www.tao.lu/Ontologies/generis.rdf#userLastName   str
  email      http://www.tao.lu/Ontologies/generis.rdf#userMail       str
  dataLang   http://www.tao.lu/Ontologies/generis.rdf#userDefLg      enum
  uiLang     http://www.tao.lu/Ontologies/generis.rdf#userUILg       enum
  login      http://www.tao.lu/Ontologies/generis.rdf#login          str
  roles      http://www.tao.lu/Ontologies/generis.rdf#userRoles      list
  password   http://www.tao.lu/Ontologies/generis.rdf#password       str
  timezone   http://www.tao.lu/Ontologies/generis.rdf#userTimezone   enum


### User/roles
TODO (hash out how to accomplish this)
- get these dynamically
- can I get these currently from tao actions

### ui/component
- init(config : { renderTo, replace })
- destroy()
- render(container)
- show()
- hide()
- enable()
- disable()
- is(state)
- setState(state, flag)
- getContainer()
- getElement()
- getTemplate()
- setTemplate()

### ui/form

### ui/form/field

### ui/form/generis/user
Fields
- label
- first name
- last name
- email
- data language
- interface language
- roles
- password
