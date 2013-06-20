<?php
namespace Sta\Dbal\Logging;

use Doctrine\DBAL\Logging\SQLLogger;
use Sta\Util\StringFormats;

/**
 * A SQL logger that logs to the standard output using echo/var_dump.
 *
 * @author  Stavarengo
 */
class EchoSQLLogger implements SQLLogger
{
	/**
	 * {@inheritdoc}
	 */
	public function startQuery($sql, array $params = null, array $types = null)
	{
		echo '<pre>' . StringFormats::printSQL($sql, true) . '</pre>';

		if ($params) {
			var_dump($params);
		}

		if ($types) {
			var_dump($types);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function stopQuery()
	{

	}
}
