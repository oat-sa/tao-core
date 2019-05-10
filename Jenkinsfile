pipeline {
    agent any
    stages {
        stage('Initialization') {
            environment {
                JENKINS_TOKEN = credentials('JENKINS_TOKEN')
            }
            steps {
                sh 'printenv'
                sh(
                    label : 'Create build build directory',
                    script: 'mkdir -p build'
                )

                withCredentials([usernamePassword(credentialsId: 'jenkins_github_token', usernameVariable: 'GIT_USERNAME', passwordVariable: 'GIT_TOKEN',)]) {
                    sh '''
changeBranch=$CHANGE_BRANCH
branch="${noz:-$BRANCH_NAME}"
echo "using ${branch}"
docker run --rm  \\
-e "GITHUB_ORGANIZATION=oat-sa" \\
-e "GITHUB_SECRET=${GIT_TOKEN}"  \\
registry.service.consul:4444/tao/dependency-resolver oat:dependencies:resolve --main-branch ${branch} --repository-name oat-sa/tao-core > build/composer.json
                    '''
                    sh '''
    cat build/composer.json
                    '''
                }
            }
        }
    }
}
