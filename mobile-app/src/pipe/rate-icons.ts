import { Pipe, PipeTransform } from "@angular/core";

@Pipe({
    name: 'rateicons'
})
export class RateIconsPipe implements PipeTransform 
{
    public transform(input:number): string
    {
        let icons;
        switch(input)
        {
            case 1: icons = 'ios-thumbs-down'; break;
            case 2: icons = 'ios-sad'; break;
            case 3: icons = 'ios-thumbs-up'; break;
            case 4: icons = 'ios-happy'; break;
            case 5: icons = 'ios-medal'; break;
            default: icons = 'ios-star'; break;
        }

        return icons;
    }   
}

@Pipe({
    name: 'ratecolors'
})
export class RateColorsPipe implements PipeTransform 
{
    public transform(input:number): string
    {
        let icons;
        switch(input)
        {
            case 1: icons = 'danger'; break;
            case 2: icons = 'oranges'; break;
            case 3: icons = 'bluesexy'; break;
            case 4: icons = 'greentea'; break;
            case 5: icons = 'secondary'; break;
            default: icons = 'dark'; break;
        }

        return icons;
    }
}

@Pipe({
    name: 'ratewords'
})
export class RateWordsPipe implements PipeTransform 
{
    public transform(input:number): string
    {
        let icons;
        switch(input)
        {
            case 1: icons = 'bad!'; break;
            case 2: icons = 'sad'; break;
            case 3: icons = 'it\'s ok'; break;
            case 4: icons = 'good'; break;
            case 5: icons = 'very good!'; break;
            default: icons = 'yup'; break;
        }

        return icons;
    }
}