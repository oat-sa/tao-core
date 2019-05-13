pipeline {
    agent any
    stages {
        stage('Initialization') {
            environment {
                GITHUB_ORGANIZATION='oat-sa'
                REPO_NAME='oat-sa/tao-core'
            }
            steps {
                sh(
                    label : 'Create build build directory',
                    script: 'mkdir -p build'
                )

                withCredentials([usernamePassword(credentialsId: 'tmpaccess', usernameVariable: 'GIT_USER', passwordVariable: 'GIT_TOKEN')]) {
                    sh 'printenv'
                    sh(
                        label : 'Run the Dependency Resolver',
                        script: '''
changeBranch=$CHANGE_BRANCH
TEST_BRANCH="${changeBranch:-$BRANCH_NAME}"
echo "select branch : ${TEST_BRANCH}"
docker run --rm  \\
-e "GITHUB_ORGANIZATION=${GITHUB_ORGANIZATION}" \\
-e "GITHUB_SECRET=${GIT_TOKEN}"  \\
registry.service.consul:4444/tao/dependency-resolver oat:dependencies:resolve --main-branch ${TEST_BRANCH} --repository-name ${REPO_NAME} > build/composer.json
                        '''
                    )
                }
            }
        }

        stage('Tests') {
            parallel {
                stage('Backend Tests') {
                    agent {
                        docker {
                            image 'alexwijn/docker-git-php-composer'
                            reuseNode true
                        }
                    }
                    options {
                        skipDefaultCheckout()
                    }
                    steps {
                        sh(
                            label: 'Install/Update sources from Composer',
                            script: 'cd build && composer update --no-interaction --no-ansi --no-progress'
                        )
                        sh(
                            label: 'Add phpunit',
                            script: 'cd build && composer require phpunit/phpunit:^4.8'
                        )
                        sh(
                            label: 'Run backend tests',
                            script: 'cd build && ./vendor/bin/phpunit tao/test/unit'
                        )
                    }
                }
                stage('Frontend Tests') {
                    agent {
                        docker {
                            image 'alekzonder/puppeteer'
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
                        sh(
                            label: 'Setup frontend toolchain',
                            script: 'cd build/tao/views/build && npm install'
                        )
                        sh (
                            label : 'Run frontend tests',
                            script: '''
cd build/tao/views/build
ls -l
npx grunt connect:test taotest  --no-color --force
                            '''
                        )
                    }
                }
            }
        }
    }
}
