#!/bin/bash

TEMPERATURE_FILE="/home/seeschloss/http/veranda.seos.fr/data/temperature.tsv"
TEMPERATURE_FILE_VERANDA="/home/seeschloss/http/veranda.seos.fr/data/temperature-veranda.tsv.bz2"
TEMPERATURE_FILE_SALON="/home/seeschloss/http/veranda.seos.fr/data/temperature-salon.tsv.bz2"
TEMPERATURE_FILE_ENTREE="/home/seeschloss/http/veranda.seos.fr/data/temperature-entree.tsv.bz2"

test -z "$SSH_ORIGINAL_COMMAND" && SSH_ORIGINAL_COMMAND=$@
case "$SSH_ORIGINAL_COMMAND" in
	temperature-veranda)
		echo "Mise à jour de la température..."
		cat > "$TEMPERATURE_FILE_VERANDA"
		php /home/seeschloss/http/veranda.seos.fr/cli/temperature-tsv.php > "$TEMPERATURE_FILE"
		;;
	temperature-entree)
		echo "Mise à jour de la température..."
		cat > "$TEMPERATURE_FILE_ENTREE"
		php /home/seeschloss/http/veranda.seos.fr/cli/temperature-tsv.php > "$TEMPERATURE_FILE"
		;;
	temperature-salon)
		echo "Mise à jour de la température..."
		cat > "$TEMPERATURE_FILE_SALON"
		php /home/seeschloss/http/veranda.seos.fr/cli/temperature-tsv.php > "$TEMPERATURE_FILE"
		;;
	temperature*|photo*|video*)
		php /home/seeschloss/http/veranda.seos.fr/cli/ssh.php
		;;
	scp*)
		$SSH_ORIGINAL_COMMAND
		;;
esac
