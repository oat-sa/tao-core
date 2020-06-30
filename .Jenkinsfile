
final def REPO_REGEX = /^https:\/\/github\.com\/(?<org>[a-z0-9-]+)\/(?<repo>[a-z0-9-]+)\.git$/
final def TAO_EXTENSION_REGEX = /return\s*(\[|array\()\s*\'name\'\s*=>\s*\'([a-zA-Z0-9]+)\'/

pipeline {
    agent {
        label 'builder'
    }
    environment {
        phpMinimumCoverage = 90
    }
    stages {
        stage('Resolve TAO dependencies') {
            steps {
               // Extract repository information
               script {
                    def matcher = GIT_URL =~ REPO_REGEX
                    def githubOrganization = ""
                    def repoName = ""

                    if (matcher.matches()) {
                        githubOrganization = matcher.group("org")
                        repoName = matcher.group("repo")
                        echo "Extracting repository information. GITHUB_ORGANIZATION: '$githubOrganization' REPO_NAME: '$repoName'"
                    }
                    else {
                        echo "Couldn't extract repository information from GIT_URL environment variable."
                        currentBuild.result = 'FAILURE'
                    }

                    env.githubOrganization = githubOrganization
                    env.repoName = repoName
                }
                // Extract TAO extension information
                script {
                    def manifest = readFile 'manifest.php'
                    def matcher = manifest =~ TAO_EXTENSION_REGEX

                    def extension = matcher[0][2]
                    if (extension == null) {
                        echo "Couldn't extract extension name from manifest file."
                        currentBuild.result = 'FAILURE'
                    }
                    else {
                        echo "Extracting extension name from manifest file. Extension name: '$extension'"
                    }

                    env.extension = extension
                }
                sh(
                    label : 'Create build build directory',
                    script: 'mkdir -p build'
                )
                withCredentials([string(credentialsId: 'jenkins_github_token', variable: 'GIT_TOKEN')]) {
                    sh(
                        label : 'Run the Dependency Resolver',
                        script: '''
changeBranch=$CHANGE_BRANCH
TEST_BRANCH="${changeBranch:-$BRANCH_NAME}"
echo "select branch : ${TEST_BRANCH}"
docker run --rm  \\
-e "GITHUB_ORGANIZATION=${githubOrganization}" \\
-e "GITHUB_SECRET=${GIT_TOKEN}"  \\
tao/dependency-resolver oat:dependencies:resolve --main-branch ${TEST_BRANCH} --repository-name $githubOrganization/$repoName > build/dependencies.json

cat > build/composer.json <<- composerjson
{
  "repositories": [
      {
        "type": "vcs",
        "url": "https://github.com/$githubOrganization/$repoName",
        "no-api": true
      }
    ],
composerjson
tail -n +2 build/dependencies.json >> build/composer.json
                        '''
                    )
                    sh(
                        label: 'composer.json',
                        script: 'cat build/composer.json'
                    )
                }
            }
        }
        stage('Install') {
            agent {
                docker {
                    image 'dockermisi/php_base_ci:0.0.2'
                    args "-v $BUILDER_CACHE_DIR/composer:/tmp/.composer-cache -e COMPOSER_CACHE_DIR=/tmp/.composer-cache"
                    reuseNode true
                }
            }
            environment {
                HOME = '.'
            }
            options {
                skipDefaultCheckout()
            }
            steps {
                dir('build') {
                    sh(
                        label: 'Install/Update sources from Composer',
                        script: 'COMPOSER_DISCARD_CHANGES=true composer install --prefer-dist --no-interaction --no-ansi --no-progress --no-suggest'
                    )
                    sh(
                        label: 'Add phpunit',
                        script: 'composer require phpunit/phpunit:^8.5 --no-progress'
                    )
                    sh(
                        label: "Extra filesystem mocks",
                        script: '''
mkdir -p taoQtiItem/views/js/mathjax/ && touch taoQtiItem/views/js/mathjax/MathJax.js
mkdir -p tao/views/locales/en-US/
    echo "{\\"serial\\":\\"${BUILD_ID}\\",\\"date\\":$(date +%s),\\"version\\":\\"3.3.0-${BUILD_NUMBER}\\",\\"translations\\":{}}" > tao/views/locales/en-US/messages.json
mkdir -p tao/views/locales/en-US/
                        '''
                    )
                }
            }
        }
        stage('Tests') {
            parallel {
                stage('Backend Tests') {
                    when {
                        expression {
                            fileExists("build/$extension/test/unit")
                        }
                    }
                    agent {
                        docker {
                            image 'dockermisi/php_base_ci:0.0.2'
                            reuseNode true
                        }
                    }
                    options {
                        skipDefaultCheckout()
                    }
                    steps {
                        dir('build'){
                            sh(
                                label: 'Run backend tests',
                                script: "./vendor/bin/phpunit $extension/test/unit -c phpunit_full.xml"
                            )
                            sh(
                                label: 'Code coverage',
                                script: "php vendor/bin/coverage-check clover.xml $phpMinimumCoverage"
                            )
                        }
                    }
                }
                stage('Frontend Tests') {
                    when {
                        expression {
                            fileExists("build/$extension/views/build/grunt/test.js")
                        }
                    }
                    agent {
                        docker {
                            image 'btamas/puppeteer-git'
                            args "-v $BUILDER_CACHE_DIR/npm:/tmp/.npm-cache -e npm_config_cache=/tmp/.npm-cache"
                            reuseNode true
                        }
                    }
                    environment {
                        HOME = '.'
                    }
                    options {
                        skipDefaultCheckout()
                    }
                    steps {
                        dir('build/tao/views/build') {
                            sh(
                                label: 'Install tao-core frontend extensions',
                                script: 'npm install'
                            )
                            sh (
                                label : 'Run frontend tests',
                                script: "npx grunt connect:test ${extension.toLowerCase()}test"
                            )
                        }
                    }
                }
            }
        }
    }
    post {
        always {
            cleanWs disableDeferredWipeout: true
        }
    }
}
