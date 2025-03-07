{{/* Expand the name of the chart. */}}
{{- define "erebor.name" -}}
{{- default $.Chart.Name $.Values.nameOverride | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/* Create a default fully qualified app name.
We truncate at 63 chars because some Kubernetes name fields are limited to this (by the DNS naming spec).
If release name contains chart name it will be used as a full name. */}}
{{- define "erebor.fullname" -}}
{{- if .Values.fullnameOverride }}
{{- .Values.fullnameOverride | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- $name := default .Chart.Name .Values.nameOverride }}
{{- if contains $name .Release.Name }}
{{- .Release.Name | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- printf "%s-%s" .Release.Name $name | trunc 63 | trimSuffix "-" }}
{{- end }}
{{- end }}
{{- end }}

{{/*
Create chart name and version as used by the chart label.
*/}}
{{- define "erebor.chart" -}}
{{- printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | trunc 63 | trimSuffix "-" }}
{{- end }}

{{- /* erebor.version is the version of the deployed NLU Service.
Used as the container image tag */}}
{{ define "erebor.version" -}}
{{ default .Chart.AppVersion .Values.imagesConfiguration.global.tag }}
{{- end -}}

{{/* Common labels found at : https://helm.sh/docs/chart_best_practices/labels/#standard-labels*/}}
{{- define "erebor.commonLabels" -}}
{{ include "erebor.selectorLabels" . }}
app.kubernetes.io/part-of: erebor
app.kubernetes.io/version: {{ include "erebor.version" . }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
helm.sh/chart: {{ include "erebor.chart" . }}
{{- if .Values.globalLabels }}
{{ toYaml .Values.globalLabels }}
{{- end -}}
{{- end }}

{{- define "erebor.globalAnnotations" -}}
{{- if .Values.globalAnnotations }}
{{- with .Values.globalAnnotations }}
{{- toYaml . }}
{{- end -}}
{{- end -}}
{{- end }}

{{- define "erebor.selectorLabels" -}}
app.kubernetes.io/instance: {{ include "erebor.fullname" . }}
{{- end }}

{{- /* erebor.namespace is the default deployment namespace for the release */}}
{{ define "erebor.namespace" -}}
{{ default "default" .Release.Namespace }}
{{- end -}}

{{- /* erebor.imagePullSecrets will build the string array of secret name of ImagePullSecrets
  at the condition that either .Values.deployment.imagePullSecrets or .Values.managedImagePullSecret
  is not empty */}}
{{ define "erebor.imagePullSecrets" -}}
{{- if or .Values.imagePullSecrets .Values.managedImagePullSecret -}}
imagePullSecrets:
{{- if .Values.managedImagePullSecret }}
  - name: managed-regcred-{{ include "erebor.fullname" . }}
{{- end -}}
{{- with .Values.imagePullSecrets -}}
{{ toYaml . | nindent 2 }}
{{- end -}}
{{- end -}}
{{- end -}}

{{- /*** Image URL Section ***/ -}}
{{- /* erebor.globalImage.repo */}}
{{ define "erebor.globalImage.repo" -}}
{{ default "" .Values.imagesConfiguration.global.repo }}
{{- end -}}

{{- /* erebor.globalImage.tag */}}
{{ define "erebor.globalImage.tag" -}}
{{ default .Chart.AppVersion .Values.imagesConfiguration.global.tag }}
{{- end -}}

{{- /* erebor.globalImage.url */}}
{{ define "erebor.globalImage.url" -}}
{{ printf "%s:%s" (include "erebor.globalImage.repo" .) (include "erebor.globalImage.tag" .)}}
{{- end -}}

{{- /* erebor.symfonyImage.repo */}}
{{ define "erebor.symfonyImage.repo" -}}
{{ default (include "erebor.globalImage.repo" .) .Values.imagesConfiguration.custom.symfony.repo }}
{{- end -}}

{{- /* erebor.symfonyImage.tag */}}
{{ define "erebor.symfonyImage.tag" -}}
{{ default (include "erebor.globalImage.tag" .) .Values.imagesConfiguration.custom.symfony.tag }}
{{- end -}}

{{- /* erebor.symfonyImage.url */}}
{{ define "erebor.symfonyImage.url" -}}
{{ printf "%s:%s" (include "erebor.symfonyImage.repo" .) (include "erebor.symfonyImage.tag" .) }}
{{- end -}}

{{- /* erebor.nginxImage.repo */}}
{{ define "erebor.nginxImage.repo" -}}
{{ default (include "erebor.globalImage.repo" .) .Values.imagesConfiguration.custom.front.repo }}
{{- end -}}

{{- /* erebor.nginxImage.tag */}}
{{ define "erebor.nginxImage.tag" -}}
{{ default (include "erebor.globalImage.tag" .) .Values.imagesConfiguration.custom.front.tag }}
{{- end -}}

{{- /* erebor.nginxImage.url */}}
{{ define "erebor.nginxImage.url" -}}
{{ printf "%s:%s" (include "erebor.nginxImage.repo" .) (include "erebor.nginxImage.tag" .) }}
{{- end -}}