<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Sta\Dbal\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Tipo para colunas que armazenam valores percentuais.
 */
class PercentageType extends \Doctrine\DBAL\Types\Type
{

	const PERCENTAGE = 'percentage';

	public function getName()
	{
		return self::PERCENTAGE;
	}

	public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		$fieldDeclaration['precision'] = (!isset($fieldDeclaration['precision']) || empty($fieldDeclaration['precision']))
			? 18 : $fieldDeclaration['precision'];
		$fieldDeclaration['scale']     = (!isset($fieldDeclaration['scale']) || empty($fieldDeclaration['scale']))
			? 15 : $fieldDeclaration['scale'];
		return $platform->getDecimalTypeDeclarationSQL($fieldDeclaration);
	}

	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		return (null === $value) ? null : (float)$value;
	}

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
