pipeline {
    agent any
    stages {
        stage('Initialization') {
            steps {
                sh(
                    label : 'Create build build directory',
                    script: 'mkdir -p build/resolved'
                )
                sh '''
echo BRANCH_NAME :  "$BRANCH_NAME"
                '''
            }
        }
        stage('Resolve dependencies') {
                agent {
                    docker {
                        image 'tao/dependency-resolver'
                        registryUrl 'https://registry.service.consul:4444'
                        reuseNode true
                    }
                }
                environment {
                    GITHUB_ORGANIZATION='oat-sa'
                }
                options {
                    skipDefaultCheckout()
                }
                steps {
                    sh '''
pwd
ls -alh
echo "$GITHUB_ORGANIZATION"
echo "$BRANCH_NAME"
                    '''

                }
        }
    }
}
