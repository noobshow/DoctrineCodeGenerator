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

namespace Doctrine\CodeGenerator\Listener;

use Doctrine\CodeGenerator\Builder\Manipulator;
use Doctrine\CodeGenerator\GeneratorEvent;

/**
 * Each property is turned to protected and getters/setters are added.
 */
class DocListener extends AbstractCodeListener
{
    public function onGenerateProperty(GeneratorEvent $event)
    {
        $node        = $event->getNode();
        $type        = $node->getAttribute('type') ?: 'mixed';

        $node->setDocComment(<<<EPM
/**
 * @var $type
 */
EPM
);
    }

    public function onGenerateGetter(GeneratorEvent $event)
    {
        $node         = $event->getNode();
        $type         = $this->getMethodsPropertyType($node);
        $propertyName = $this->getPropertyName($node);

        $node->setDocComment(<<<EPM
/**
 * Return $propertyName
 *
 * @return $type
 */
EPM
);
    }

    public function onGenerateSetter(GeneratorEvent $event)
    {
        $node         = $event->getNode();
        $type         = $this->getMethodsPropertyType($node);
        $propertyName = $this->getPropertyName($node);

        $node->setDocComment(<<<EPM
/**
 * Set $propertyName
 *
 * @param $type \$$propertyName
 */
EPM
);
    }

    private function getMethodsPropertyType($method)
    {
        $property = $method->getAttribute('property');
        $type     = null;

        if ($property) {
            $type = $property->getAttribute('type');
        } else if (preg_match('(^(set|get)(.*+)$)', $method->getName(), $match)) {
            $propertyName = lcfirst($match[2]);
            $class        = $method->getClass();

            if ( ! $class->hasProperty($propertyName)) {
                return 'mixed';
            }

            $property = $class->getProperty($propertyName);
            $type     = $property->getAttribute('type');
        }

        return $type ?: 'mixed';
    }

    private function getPropertyName($node)
    {
        $property = $node->getAttribute('property');

        if ($property) {
            return $property->getName();
        }

        return lcfirst(substr($node->getName(), 3));
    }
}

