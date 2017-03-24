### TODO:
- Move form styles to ui/form/form.scss
- Incorporate fieldset and legend
- Create library of reusable validators
- Reduce api to a minimum for consistent usage
	- Could promisify
- Alphabetize (or create a consistent way to place properties)



### Users resource
  ACTION    VERB      PATH                RETURN (JSON)
  @index    [GET]     tao/users           [ user ]
* @new      [GET]     tao/users/new       { user, schema }
* @create   [POST]    tao/users           created?
  @show     [GET]     tao/users/:id       user
* @edit     [GET]     tao/users/:id/edit  { user, schema }
* @update   [PATCH]   tao/users/:id       updated?
  @destroy  [DELETE]  tao/users/:id       destroyed?

TODO
* - create @new action
  	- return unique label (within a user object)
  	- stub rdf user schema
* - create @create action
  	- creates user
  	- returns { success: bool, status: int/str, data: [], errors: [] }
  		- if success then 201
  		- else if user exists then 409
  		- else if user validation error then 400
  		- else 500
  - create @edit action
  	- return user
  	- stub rdf user schema
  - create @update action
  	- updates user
  	- returns { success: bool, status: int/str, data: [], errors: [] }
  		- if success then 200
  		- else if user validation error then 400
  		- else 500

### Users pages
  TITLE                 STRUCTURE  EXT  SECTION       CONTROLLER
  Manage users          users      tao  list_users
* Add a user            users      tao  add_user      .../users/add
* Edit a user           users      tao  edit_user     .../?
  Manage roles          users      tao  manage_roles
  Manage Access Rights  users      tao  manage_acl

TODO
* - add_user
+ 	- move js to controller
+ 	- add on submit (to tao/users/new)
  		- 2** - show success message and redirect to ?
  		- 4** - show errors on form
  		- 5** - redirect to error page
  - edit_user
  	- create js controller
  	- create on submit (to tao/users/edit)
  		- 2** - show success mesagge and redirect to ?
  		- 4** - show errors on form
  		- 5** - redirect to error page

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

### ui/form
TODO
* - index fields property by field name

### ui/form/field
TODO
* - create addErrors()
	- color red
	- list errors in field

### Users fields
- label
- first name
- last name
- email
- data language
- interface language
- roles
- password
