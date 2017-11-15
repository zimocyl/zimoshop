        ##判断是否具有%field%条件
        $filter_%field% = input('filter_%field%','');
        if ($filter_%field% !== ''){
        #为where(null)增加条件查询
        $builder->where('%field%','like','%'.$filter_%field%.'%');
        $filter['filter_%field%'] = $filter_%field%;
        }
