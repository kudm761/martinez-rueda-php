## Martinez-Rueda polygon boolean operations algorithm
PHP implementation of original algorithm <http://www4.ujaen.es/~fmartin/bool_op.html>

Algorithm is used for computing Boolean operations on polygons:
- union
- difference
- intersection
- xor
## Usage
Input parameter is a multipolygon - an array of polygons. And each polygon is an array of points x,y.
```
    $data = [[[-1, 4], [-3, 4], [-3, 0], [-3, -1], [-1, -1], [-1, -2], [2, -2], [2, 1], [-1, 1], [-1, 4]]];
    $subject = new \MartinezRueda\Polygon($data);
    
    $data = [[[-2, 5], [-2, 0], [3, 0], [3, 3], [2, 3], [2, 2], [0, 2], [0, 5], [-2, 5]]];
    $clipping = new \MartinezRueda\Polygon($data);
    
    $result = (new \MartinezRueda\Algorithm())->getUnion($subject, $clipping);
    
    echo json_encode($result->toArray()), PHP_EOL;
    
    // Result is:
    // [[[2,3],[2,2],[0,2],[0,5],[-2,5],[-2,4],[-3,4],[-3,0],[-3,-1],[-1,-1],[-1,-2],[2,-2],[2,0],[3,0],[3,3],[2,3]]]
```
