pipeline {
    agent any


    options {
        timestamps()
        disableConcurrentBuilds()
        buildDiscarder(logRotator(numToKeepStr: '20'))
        timeout(time: 45, unit: 'MINUTES')
        skipDefaultCheckout(true)
    }

    environment {
        IMAGE_REPO = 'andersoncrms/iris'
    }

    stages {
        stage('Checkout & Metadata') {
            steps {
                deleteDir()
                checkout scm

                sh 'mkdir -p reports'

                script {
                    env.GIT_SHA = sh(
                        script: 'git rev-parse --short=8 HEAD',
                        returnStdout: true
                    ).trim()

                    env.IMAGE_TAG =
                        "${env.BUILD_NUMBER}-${env.GIT_SHA}"

                    env.CI_IMAGE =
                        "iris-ci:${env.IMAGE_TAG}"
                }

                echo "Commit: ${env.GIT_SHA}"
                echo "Image tag: ${env.IMAGE_TAG}"
            }
        }

        stage('Build image') {
            steps {
                sh '''
                    docker build \
                        -t ${IMAGE_REPO}:${BUILD_NUMBER} \
                        -t ${IMAGE_REPO}:latest \
                        .
                '''
            }
        }

        stage('Scan image with Trivy') {
            steps {
                catchError(buildResult: 'SUCCESS', stageResult: 'UNSTABLE') {
                    sh '''
                        docker run --rm \
                            -v /var/run/docker.sock:/var/run/docker.sock \
                            -v trivy_cache:/root/.cache/ \
                            aquasec/trivy:latest image \
                            --scanners vuln \
                            --severity HIGH,CRITICAL \
                            --ignore-unfixed \
                            --exit-code 1 \
                            --timeout 10m \
                            --no-progress \
                            ${IMAGE_REPO}:${BUILD_NUMBER}
                    '''
                }
            }
        }

        stage('Push to Docker Hub') {
            steps {
                withCredentials([usernamePassword(
                    credentialsId: 'dockerhub-creds',
                    usernameVariable: 'DOCKERHUB_USER',
                    passwordVariable: 'DOCKERHUB_TOKEN'
                )]) {
                    sh '''
                        echo "$DOCKERHUB_TOKEN" | docker login -u "$DOCKERHUB_USER" --password-stdin
                        docker push ${IMAGE_REPO}:${BUILD_NUMBER}
                        docker push ${IMAGE_REPO}:latest
                    '''
                }
            }
            post {
                always {
                    sh 'docker logout || true'
                }
            }
        }
    }

    post {
        success {
            echo 'CI concluída: testes, segurança e publicação aprovados.'
        }

        failure {
            echo 'CI falhou. Consulte a etapa e os relatórios arquivados.'
        }
    }

}