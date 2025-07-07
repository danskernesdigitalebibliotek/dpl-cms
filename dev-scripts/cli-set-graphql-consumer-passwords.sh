#!/usr/bin/env bash

# shellcheck source=/dev/null
set -aeo pipefail; source .env; set +a

# Check if all environment variables are defined
if [[ -z $BNF_GRAPHQL_CONSUMER_USER_PASSWORD ]]; then
  echo "Error: BNF_GRAPHQL_CONSUMER_USER_PASSWORD is missing!"
  exit 1
fi
if [[ -z $NEXT_PUBLIC_GO_GRAPHQL_CONSUMER_USER_PASSWORD ]]; then
  echo "Error: NEXT_PUBLIC_GO_GRAPHQL_CONSUMER_USER_PASSWORD is missing!"
  exit 1
fi

drush upwd bnf_graphql $BNF_GRAPHQL_CONSUMER_USER_PASSWORD
drush upwd go_graphql $NEXT_PUBLIC_GO_GRAPHQL_CONSUMER_USER_PASSWORD
